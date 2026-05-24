<?php

/**
 * Herramienta administrativa: Reinicializar base de datos
 * 
 * Ejecutar: php artisan tinker
 * Luego: include('scripts/reset-database.php');
 * 
 * Elimina TODOS los datos:
 * - Notas
 * - Categorías
 * - Etiquetas
 * - Configuraciones de usuarios
 * - Usuarios
 */

// Verificar que se ejecuta desde CLI (no desde web)
if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ejecutarse desde la línea de comandos.\n");
}
echo php_sapi_name();
echo phpversion();

use App\Models\Note;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserSetting;

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "     HERRAMIENTA ADMINISTRATIVA - DKNotes\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "⚠️  ADVERTENCIA: Esta acción ELIMINARÁ TODOS LOS DATOS.\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Mostrar resumen de lo que se eliminará
echo "Resumen de datos actuales:\n";
echo "───────────────────────────────────────────────────────────\n";
echo "  Notas:           " . Note::count() . "\n";
echo "  Categorías:      " . Category::count() . "\n";
echo "  Etiquetas:       " . Tag::count() . "\n";
echo "  Configuraciones: " . UserSetting::count() . "\n";
echo "  Usuarios:        " . User::count() . "\n";
echo "───────────────────────────────────────────────────────────\n\n";

// Confirmación con palabra clave
echo "Para confirmar la eliminación TOTAL de todos los datos,\n";
echo "escribe exactamente: ELIMINAR\n\n";

$confirmacion = readline("> ");

if ($confirmacion !== 'ELIMINAR') {
    echo "\n❌ Cancelado. No se eliminó ningún dato.\n\n";
    exit;
}

echo "\n⚠️  Última oportunidad. ¿Estás absolutamente seguro?\n";
$ultima = readline("Escribe 'SI' para continuar: ");

if ($ultima !== 'SI') {
    echo "\n❌ Cancelado. No se eliminó ningún dato.\n\n";
    exit;
}

echo "\n🔄 Eliminando datos...\n";

try {
    // 1. Eliminar notas
    $notesCount = Note::count();
    Note::truncate();
    echo "  ✅ Notas eliminadas: {$notesCount}\n";
    
    // 2. Eliminar etiquetas
    $tagsCount = Tag::count();
    Tag::truncate();
    echo "  ✅ Etiquetas eliminadas: {$tagsCount}\n";
    
    // 3. Eliminar configuraciones de usuarios
    $settingsCount = UserSetting::count();
    UserSetting::truncate();
    echo "  ✅ Configuraciones eliminadas: {$settingsCount}\n";
    
    // 4. Eliminar categorías
    $categoriesCount = Category::count();
    Category::truncate();
    echo "  ✅ Categorías eliminadas: {$categoriesCount}\n";
    
    // 5. Eliminar usuarios
    $usersCount = User::count();
    User::truncate();
    echo "  ✅ Usuarios eliminados: {$usersCount}\n";
    
    echo "\n═══════════════════════════════════════════════════════════\n";
    echo "✅ Base de datos completamente reinicializada.\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR CRÍTICO: " . $e->getMessage() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
    echo "   La operación se detuvo. La base de datos puede estar en un estado inconsistente.\n\n";
    exit(1);
}