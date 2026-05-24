<?php

/**
 * Herramienta administrativa: Crear usuario administrador
 * 
 * Ejecutar: php artisan tinker
 * Luego: include('scripts/create-admin.php');
 * 
 * Crea un nuevo usuario con rol de administrador.
 */

// Verificar que se ejecuta desde CLI (no desde web)
if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ejecutarse desde la línea de comandos.\n");
}


use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Función para leer contraseña oculta
function readPassword($prompt = "Contraseña: ")
{
    echo $prompt;
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows - no se puede ocultar fácilmente, usar readline normal
        return readline();
    } else {
        // Unix/Linux - usar stty para ocultar
        system('stty -echo');
        $password = rtrim(fgets(STDIN), "\n");
        system('stty echo');
        echo "\n";
        return $password;
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "     CREAR USUARIO ADMINISTRADOR - DKNotes\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Solicitar nombre
$name = '';
while (empty($name)) {
    $name = trim(readline("Nombre completo: "));
    if (empty($name)) {
        echo "❌ El nombre no puede estar vacío.\n";
    }
}

// Solicitar email
$email = '';
while (empty($email)) {
    $email = trim(readline("Correo electrónico: "));
    if (empty($email)) {
        echo "❌ El correo no puede estar vacío.\n";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "❌ Formato de correo inválido.\n";
        $email = '';
    } else {
        // Verificar si ya existe
        $existing = User::where('email', $email)->first();
        if ($existing) {
            echo "❌ Ya existe un usuario con ese correo.\n";
            $email = '';
        }
    }
}

// Solicitar contraseña (con verificación)
$password = '';
$passwordConfirm = '';

while (empty($password)) {
    $password = readPassword("Contraseña: ");
    if (strlen($password) < 6) {
        echo "❌ La contraseña debe tener al menos 6 caracteres.\n";
        $password = '';
        continue;
    }
    
    $passwordConfirm = readPassword("Confirmar contraseña: ");
    if ($password !== $passwordConfirm) {
        echo "❌ Las contraseñas no coinciden.\n";
        $password = '';
        $passwordConfirm = '';
    }
}

// Mostrar resumen
echo "\n───────────────────────────────────────────────────────────\n";
echo "Resumen:\n";
echo "  Nombre:  {$name}\n";
echo "  Email:   {$email}\n";
echo "  Rol:     Administrador\n";
echo "───────────────────────────────────────────────────────────\n";

// Confirmación
$confirmar = readline("\n¿Crear usuario? (s/n): ");
if (strtolower($confirmar) !== 's') {
    echo "\n❌ Cancelado. No se creó el usuario.\n\n";
    exit;
}

// Crear usuario
try {
    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make($password),
        'is_admin' => true,
        'email_verified_at' => now(),
    ]);
    
    echo "\n═══════════════════════════════════════════════════════════\n";
    echo "✅ Usuario administrador creado exitosamente.\n";
    echo "   ID: {$user->id}\n";
    echo "   Nombre: {$user->name}\n";
    echo "   Email: {$user->email}\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "   Línea: " . $e->getLine() . "\n\n";
    exit(1);
}