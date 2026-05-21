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
    Route::get('/notes/filter/{category?}', [App\Http\Controllers\NoteController::class, 'filter'])->name('notes.filter');
    Route::post('/notes/sync', [App\Http\Controllers\NoteController::class, 'sync'])->name('notes.sync');
    Route::get('/notes/create', [App\Http\Controllers\NoteController::class, 'create'])->name('notes.create');
    Route::post('/notes', [App\Http\Controllers\NoteController::class, 'store'])->name('notes.store');
    // subir notas
    Route::post('/notes/upload', [App\Http\Controllers\NoteController::class, 'upload'])->name('notes.upload');
    // eliminar todas las notas
    Route::delete('/notes/delete-all', [App\Http\Controllers\NoteController::class, 'deleteAll'])->name('notes.delete-all');
    
    Route::get('/notes/{note}/edit', [App\Http\Controllers\NoteController::class, 'edit'])->name('notes.edit');
    Route::put('/notes/{note}', [App\Http\Controllers\NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [App\Http\Controllers\NoteController::class, 'destroy'])->name('notes.destroy');

    // Rutas genericas 
    Route::get('/notes/{note}', [App\Http\Controllers\NoteController::class, 'show'])->name('notes.show');
    Route::get('/notes', [App\Http\Controllers\NoteController::class, 'index'])->name('notes.index');

    // Configuraciones
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');

    // Categorias
    Route::post('/categories/recalculate', [App\Http\Controllers\CategoryController::class, 'recalculate'])->name('categories.recalculate');
    Route::delete('/categories/{category}', [App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');
    
    // Otras
    Route::post('/notes/take-ownership', [App\Http\Controllers\NoteController::class, 'takeOwnership'])->name('notes.take-ownership');


    // nativas Laravel
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


});

require __DIR__.'/auth.php';

