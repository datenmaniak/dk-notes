<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    //  Modificar el enrutamiento raiz, para redirigir el acceso de la app hacia login page
    Route::redirect('/', '/login');
});

//  Acceso al dashboard desabilitado
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Rutas específicas PRIMERO
    Route::get('/notes/filter/{category?}', [NoteController::class, 'filter'])->name('notes.filter');
    Route::get('notes/filter-by-tag/{tagSlug}', [NoteController::class, 'filterByTag'])->name('notes.filter.tag');
    Route::get('notes/tag/{tagSlug}', [NoteController::class, 'filterByTag'])->name('notes.filterByTag');
    Route::post('/notes/sync', [NoteController::class, 'sync'])->name('notes.sync');
    Route::get('/notes/create', [NoteController::class, 'create'])->name('notes.create');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');

    // Rutas genericas
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::get('/notes/{note}', [NoteController::class, 'show'])->name('notes.show');

    // subir notas
    Route::post('/notes/upload', [NoteController::class, 'upload'])->name('notes.upload');
    // eliminar todas las notas
    Route::delete('/notes/delete-all', [NoteController::class, 'deleteAll'])->name('notes.delete-all');

    Route::get('/notes/{note}/edit', [NoteController::class, 'edit'])->name('notes.edit');
    Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    // Configuraciones
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Categorias
    Route::post('/categories/recalculate', [CategoryController::class, 'recalculate'])->name('categories.recalculate');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Otras
    Route::post('/notes/take-ownership', [NoteController::class, 'takeOwnership'])->name('notes.take-ownership');

    // nativas Laravel
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Etiquetas
    Route::prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'index'])->name('tags.index');
        Route::post('/', [TagController::class, 'store'])->name('tags.store');
        Route::delete('/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
        Route::post('/notes/{note}/tags', [TagController::class, 'assign'])->name('notes.tags.assign');
        Route::get('/notes/{note}/tags', [TagController::class, 'getNoteTags'])->name('notes.tags.get');
    });

});

require __DIR__.'/auth.php';
