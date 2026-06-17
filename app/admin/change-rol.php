<?php

/**
 * Herramienta administrativa: Cambiar rol de usuario (admin sí/no)
 *
 * Ejecutar: php artisan tinker
 * Luego: include('scripts/change-role.php');
 */

use App\Models\User;

// Función para limpiar pantalla
function clearScreen()
{
    echo "\033[2J\033[H";
}

// Función para mostrar título
function showTitle($title)
{
    echo "\n========================================================================\n";
    echo '                    '.$title."\n";
    echo "========================================================================\n\n";
}

// Función para listar usuarios (resumido)
function listUsers()
{
    $users = User::all();

    echo "  Usuarios registrados:\n";
    echo "  ------------------------------------------------------------------------\n";

    foreach ($users as $user) {
        $admin = $user->is_admin ? 'Si' : 'No';
        printf("  ID: %-3d | %-25s | %-30s | Admin: %s\n",
            $user->id,
            $user->name,
            $user->email,
            $admin
        );
    }

    echo "  ------------------------------------------------------------------------\n";
}

// Función para encontrar usuario por email
function findUserByEmail($email)
{
    return User::where('email', $email)->first();
}

clearScreen();

showTitle('CAMBIAR ROL DE USUARIO');

// Mostrar lista de usuarios
listUsers();

echo "\n";

// Solicitar email
$email = readline('  Email del usuario: ');

$user = findUserByEmail($email);

if (! $user) {
    echo "\n  No se encontro un usuario con el email: $email\n\n";
    echo '  Presione Enter para salir...';
    readline();
    exit;
}

// Mostrar datos del usuario
echo "\n  Usuario encontrado:\n";
echo "  --------------------------------------------------\n";
echo '  Nombre: '.$user->name."\n";
echo '  Email:  '.$user->email."\n";
echo '  Rol actual: '.($user->is_admin ? 'Administrador' : 'Usuario normal')."\n";
echo "  --------------------------------------------------\n";

// Solicitar nuevo rol
echo "\n";
echo "  1. Administrador\n";
echo "  2. Usuario normal\n";
$roleOption = readline('  Nuevo rol (1-2): ');

$newRole = null;
$roleName = '';
if ($roleOption == '1') {
    $newRole = true;
    $roleName = 'Administrador';
} elseif ($roleOption == '2') {
    $newRole = false;
    $roleName = 'Usuario normal';
} else {
    echo "\n  Opcion invalida.\n\n";
    echo '  Presione Enter para salir...';
    readline();
    exit;
}

// Confirmar cambio
echo "\n  Cambiar rol de '{$user->name}' a '{$roleName}'? (s/n): ";
$confirm = strtolower(readline());

if ($confirm !== 's') {
    echo "\n  Cambio cancelado.\n\n";
    echo '  Presione Enter para salir...';
    readline();
    exit;
}

// Ejecutar cambio
try {
    $user->is_admin = $newRole;
    $user->save();

    echo "\n  Usuario actualizado correctamente.\n";
    echo "     {$user->name} ahora es {$roleName}.\n";
} catch (Exception $e) {
    echo "\n  Error al actualizar: ".$e->getMessage()."\n";
}

echo "\n";
readline('  Presione Enter para salir...');
