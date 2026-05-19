<?php

use Illuminate\Support\Facades\Auth;

Auth::loginUsingId(1);
use App\Models\Category;
use App\Models\Note;

// Simular filtro para categoría 'A'
$cat = Category::where('slug', 'a')->first();
$notes = Note::where('user_id', 1)->where('category_id', $cat->id)->get();
echo "Notas en categoría A: " . $notes->count() . "\n";
foreach($notes as $note) {
    echo "- {$note->title}\n";
}