<?php

use Illuminate\Support\Facades\Auth;

Auth::loginUsingId(1);
use App\Models\Category;
use App\Models\Note;

$output = fopen('diagnostic_result.txt', 'w');

fwrite($output, "=== CATEGORÍAS EN BD ===\n");
$categories = Category::all()->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug]);
foreach ($categories as $c) {
    fwrite($output, "{$c['id']}: {$c['name']} (slug: {$c['slug']})\n");
}

fwrite($output, "\n=== NOTAS Y SUS CATEGORÍAS ===\n");
$notes = Note::with('category')->get();
foreach ($notes as $note) {
    $catName = $note->category ? $note->category->name : 'SIN CATEGORÍA';
    fwrite($output, "Nota: {$note->title} | Categoría ID: {$note->category_id} | Nombre: {$catName}\n");
}

fwrite($output, "\n=== USUARIO ACTUAL ===\n");
$user = Auth::user();
fwrite($output, "ID: " . ($user ? $user->id : 'null') . "\n");
fwrite($output, "Nombre: " . ($user ? $user->name : 'no autenticado') . "\n");

fwrite($output, "\n=== NOTAS DEL USUARIO ===\n");
if ($user) {
    $userNotes = Note::where('user_id', $user->id)->get();
    foreach ($userNotes as $note) {
        fwrite($output, "ID: {$note->id} | Título: {$note->title} | user_id: {$note->user_id} | category_id: {$note->category_id}\n");
    }
    fwrite($output, "Total notas del usuario: " . $userNotes->count() . "\n");
} else {
    fwrite($output, "No hay usuario autenticado. Ejecuta: Auth::loginUsingId(1)\n");
}

fclose($output);
echo "Diagnóstico guardado en diagnostic_result.txt\n";