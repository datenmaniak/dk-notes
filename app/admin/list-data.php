<?php

/**
 * Herramienta administrativa: Listar usuarios, categorías y etiquetas
 * 
 * Ejecutar: php artisan tinker
 * Luego: include('scripts/list-data.php');
 */

use App\Models\User;
use App\Models\Category;
use App\Models\Tag;

// Función para limpiar pantalla
function clearScreen()
{
    echo "\033[2J\033[H";
}

// Función para mostrar título
function showTitle($title)
{
    echo "\n═══════════════════════════════════════════════════════════════\n";
    echo "                    " . $title . "\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
}

// Función para mostrar usuarios
function showUsers()
{
    $users = User::all();
    
    if ($users->isEmpty()) {
        echo "  No hay usuarios registrados.\n";
        return;
    }
    
    printf("  %-4s %-20s %-30s %-8s %-10s\n", "ID", "NOMBRE", "EMAIL", "ADMIN", "NOTAS");
    echo "  " . str_repeat("-", 80) . "\n";
    
    foreach ($users as $user) {
        $admin = $user->is_admin ? "✅" : "❌";
        $notasCount = $user->notes()->count();
        printf("  %-4d %-20.20s %-30.30s %-8s %-10d\n", 
            $user->id, 
            $user->name, 
            $user->email, 
            $admin,
            $notasCount
        );
    }
    
    echo "  " . str_repeat("-", 80) . "\n";
    echo "  Total: " . $users->count() . " usuarios\n";
}

// Función para mostrar categorías
function showCategories()
{
    $categories = Category::withCount('notes')->get();
    
    if ($categories->isEmpty()) {
        echo "  No hay categorías registradas.\n";
        return;
    }
    
    printf("  %-4s %-25s %-25s %-10s\n", "ID", "NOMBRE", "SLUG", "NOTAS");
    echo "  " . str_repeat("-", 70) . "\n";
    
    foreach ($categories as $cat) {
        printf("  %-4d %-25.25s %-25.25s %-10d\n", 
            $cat->id, 
            $cat->name, 
            $cat->slug,
            $cat->notes_count
        );
    }
    
    echo "  " . str_repeat("-", 70) . "\n";
    echo "  Total: " . $categories->count() . " categorías\n";
}

// Función para mostrar etiquetas
function showTags()
{
    $tags = Tag::withCount('notes')->get();
    
    if ($tags->isEmpty()) {
        echo "  No hay etiquetas registradas.\n";
        return;
    }
    
    printf("  %-4s %-25s %-25s %-10s\n", "ID", "NOMBRE", "SLUG", "NOTAS");
    echo "  " . str_repeat("-", 70) . "\n";
    
    foreach ($tags as $tag) {
        printf("  %-4d %-25.25s %-25.25s %-10d\n", 
            $tag->id, 
            $tag->name, 
            $tag->slug,
            $tag->notes_count
        );
    }
    
    echo "  " . str_repeat("-", 70) . "\n";
    echo "  Total: " . $tags->count() . " etiquetas\n";
}

// Función para mostrar todos
function showAll()
{
    showUsers();
    echo "\n";
    showCategories();
    echo "\n";
    showTags();
}

// Función principal del menú
function showMenu()
{
    clearScreen();
    
    echo "\n═══════════════════════════════════════════════════════════════\n";
    echo "                LISTAR DATOS - DKNotes\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    echo "  1. Usuarios\n";
    echo "  2. Categorías\n";
    echo "  3. Etiquetas\n";
    echo "  4. Todos\n";
    echo "  5. Salir\n";
    echo "\n───────────────────────────────────────────────────────────────\n";
}

// Bucle principal
while (true) {
    showMenu();
    $option = readline("  Seleccione una opción (1-5): ");
    
    echo "\n";
    
    switch ($option) {
        case '1':
            showTitle("USUARIOS");
            showUsers();
            break;
        case '2':
            showTitle("CATEGORÍAS");
            showCategories();
            break;
        case '3':
            showTitle("ETIQUETAS");
            showTags();
            break;
        case '4':
            showTitle("TODOS LOS DATOS");
            showAll();
            break;
        case '5':
            echo "  👋 Hasta luego.\n\n";
            exit;
        default:
            echo "  ❌ Opción inválida. Intente nuevamente.\n";
            break;
    }
    
    echo "\n  ";
    readline("Presione Enter para continuar...");
}