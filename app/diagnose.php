<?php

use App\Models\Category;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

// 1. Ver todas las categorías
echo "=== CATEGORÍAS EN BD ===\n";
Category::all()->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug])->each(function ($c) {
    echo "{$c['id']}: {$c['name']} (slug: {$c['slug']})\n";
});

// 2. Ver notas y sus categorías
echo "\n=== NOTAS Y SUS CATEGORÍAS ===\n";
Note::with('category')->get()->each(function ($note) {
    $catName = $note->category ? $note->category->name : 'SIN CATEGORÍA';
    echo "Nota: {$note->title} | Categoría ID: {$note->category_id} | Nombre: {$catName}\n";
});

// 3. Verificar el usuario autenticado
echo "\n=== USUARIO ACTUAL ===\n";
$user = Auth::user();
echo 'ID: '.($user ? $user->id : 'null')."\n";
echo 'Nombre: '.($user ? $user->name : 'no autenticado')."\n";

// 4. Ver notas del usuario
echo "\n=== NOTAS DEL USUARIO ===\n";
if ($user) {
    Note::where('user_id', $user->id)->get()->each(function ($note) {
        echo "ID: {$note->id} | Título: {$note->title} | user_id: {$note->user_id}\n";
    });
} else {
    echo "No hay usuario autenticado en tinker. Necesitas ejecutar: Auth::loginUsingId(1)\n";
}
