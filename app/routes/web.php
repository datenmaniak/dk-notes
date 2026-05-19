<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

     // Rutas específicas PRIMERO
    Route::post('/categories/recalculate', [App\Http\Controllers\CategoryController::class, 'recalculate'])->name('categories.recalculate');
    
    Route::delete('/categories/{category}', [App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/notes/filter/{category?}', [App\Http\Controllers\NoteController::class, 'filter'])->name('notes.filter');
    Route::post('/notes/sync', [App\Http\Controllers\NoteController::class, 'sync'])->name('notes.sync');
    Route::get('/notes/{note}/edit', [App\Http\Controllers\NoteController::class, 'edit'])->name('notes.edit');
    Route::put('/notes/{note}', [App\Http\Controllers\NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [App\Http\Controllers\NoteController::class, 'destroy'])->name('notes.destroy');


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    
    // Rutas para notas
    Route::get('/notes/{note}', [App\Http\Controllers\NoteController::class, 'show'])->name('notes.show');
    Route::get('/notes', [App\Http\Controllers\NoteController::class, 'index'])->name('notes.index');


    // Ruta genérica ÚLTIMA


});

require __DIR__.'/auth.php';

