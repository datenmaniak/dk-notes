<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
// use Illuminate\Container\Attributes\Auth;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Parsedown;

class NoteController extends Controller
{
    /**
     * Lista todas las notas del usuario autenticado
     */
    public function index()
    {
      

        // paginador  // Ajustar el digito para limitar la cantidad de notas a mostrar
        $perPage = request()->input('per_page', 5); 

        // Filtrar las notas por rol
        if (Auth::user()->is_admin) {
            // Administrador: ve todas las notas
            // $notes = Note::with('category')->get(); // reemplazado, al usar paginacion
            $notes = Note::with('category')->paginate($perPage);  // ✅ Correcto
            $totalNotas = Note::count(); // Todas las notas
        } else {
            // Usuario normal: solo sus notas
            // $notes = Auth::user()->notes()->with('category')->get();
            $notes = Auth::user();
            $totalNotas = Note::count(); // Solo sus notas
            $notes = Note::with('category')->paginate($perPage);  // ✅ Correcto
        }


        // total categorias
        $totalCategorias = Category::count();

         // Forzar la variable aquí
        $categoriasConNotas = Category::withCount('notes')->get();

         // Detectar estado de cada nota
        foreach ($notes as $note) {
            $note->status = $this->getNoteStatus($note);
        }
        
        // Retornar vista con las notas
        #return view('notes.index', compact('notes'));
        return view('notes.index', compact('notes', 'totalNotas', 'totalCategorias','categoriasConNotas'));

    }

    private function getNoteStatus(Note $note)
    {
    // Sin categoría
    if (is_null($note->category_id)) {
        return 'uncategorized';
    }
    
    // Categoría huérfana (la categoría fue eliminada)
    if ($note->category && !$note->category->exists) {
        return 'orphan_category';
    }
    
    // Nota modificada en disco (solo si file_path existe)
    if ($note->file_path && file_exists($note->file_path)) {
        $currentChecksum = md5(file_get_contents($note->file_path));
        if ($currentChecksum !== $note->checksum) {
            return 'modified';
        }
    }
    
    return 'normal';
    }
    
    /**
     * Muestra una nota específica
     */
    public function show(Note $note)
    {
        // Verificar que la nota pertenece al usuario autenticado
        if ($note->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        // Filtrar las notas por rol
        if (Auth::user()->is_admin) {
            // Administrador: ve todas las notas
            $notes = Note::with('category')->get();
        } else {
            // Usuario normal: solo sus notas
            // $notes = Auth::user()->notes()->with('category')->get();
            $notes = Auth::user();
            $notes()->with('category')->get();
        }

        // Conteo de notas por categorias
        $categoriasConNotas = Category::withCount('notes')->get();
        
        // Retornar vista con la nota
        return view('notes.show', compact('note','categoriasConNotas'));
    }

    // public function sync()
    // {
    //     Artisan::call('notes:import');
    //     return redirect()->route('notes.index')->with('success', 'Notas sincronizadas correctamente.');
    // }

    // BEGIN
    // public function sync()
    // {
    //     // Ejecutar importación
    //     Artisan::call('notes:import');
        
    //     // Actualizar checksums de notas que cambiaron en disco
    //     $notes = Note::whereNotNull('file_path')->get();
    //     $updated = 0;
        
    //     foreach ($notes as $note) {
    //         if (file_exists($note->file_path)) {
    //             $currentChecksum = md5(file_get_contents($note->file_path));
    //             if ($currentChecksum !== $note->checksum) {
    //                 $note->checksum = $currentChecksum;
    //                 $note->save();
    //                 $updated++;
    //             }
    //         }
    //     }
        
    //     $message = "Notas sincronizadas correctamente.";
    //     if ($updated > 0) {
    //         $message .= " ($updated notas actualizadas por cambios en disco)";
    //     }
        
    //     return redirect()->route('notes.index')->with('success', $message);
    // }
    // END  Comentado para DEBUG

    public function sync()
    {
        // Log para verificar que se ejecuta el botón
        Log::info('=== INICIO Sincronización desde web ===');
        
        // Ejecutar importación
        Artisan::call('notes:import');
        $output = Artisan::output();
        
        Log::info('Salida de notes:import:');
        Log::info($output);
        
        // Actualizar checksums de notas que cambiaron en disco
        $notes = Note::whereNotNull('file_path')->get();
        $updated = 0;
        
        Log::info('Notas con file_path: ' . $notes->count());
        
        foreach ($notes as $note) {
            if (file_exists($note->file_path)) {
                $currentChecksum = md5(file_get_contents($note->file_path));
                if ($currentChecksum !== $note->checksum) {
                    $note->checksum = $currentChecksum;
                    $note->save();
                    $updated++;
                    Log::info('Checksum actualizado para nota ID: ' . $note->id);
                }
            } else {
                Log::warning('Archivo no encontrado: ' . $note->file_path);
            }
        }
        
        $message = "Notas sincronizadas correctamente.";
        if ($updated > 0) {
            $message .= " ($updated notas actualizadas por cambios en disco)";
        }
        
        Log::info('=== FIN Sincronización ===');
        
        return redirect()->route('notes.index')->with('success', $message);
    }


    public function destroy(Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            abort(403);
        }

        // Filtrar las notas por rol
        if (Auth::user()->is_admin) {
            // Administrador: ve todas las notas
            $notes = Note::with('category')->get();
        } else {
            // Usuario normal: solo sus notas
            // $notes = Auth::user()->notes()->with('category')->get();
            $notes = Auth::user();
            $notes()->with('category')->get();
        }
        
        $note->delete();
        return redirect()->route('notes.index')->with('success', 'Nota eliminada.');
    }

     public function edit(Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            abort(403);
        }

        // Filtrar las notas por rol
        if (Auth::user()->is_admin) {
            // Administrador: ve todas las notas
            $notes = Note::with('category')->get();
        } else {
            // Usuario normal: solo sus notas
            // $notes = Auth::user()->notes()->with('category')->get();
            $notes = Auth::user();
            $notes()->with('category')->get();
        }
        
        $categoriasConNotas = Category::withCount('notes')->get();

        return view('notes.edit', compact('note','categoriasConNotas'));
    }


    public function update(Request $request, Note $note)
{
    if ($note->user_id !== Auth::id()) {
        abort(403);
    }

    // Filtrar las notas por rol
    if (Auth::user()->is_admin) {
        // Administrador: ve todas las notas
        $notes = Note::with('category')->get();
    } else {
        // Usuario normal: solo sus notas
        // $notes = Auth::user()->notes()->with('category')->get();
        $notes = Auth::user();
        $notes()->with('category')->get();
    }

    $request->validate([
        'title' => 'required|string|max:255',
        'content_markdown' => 'required|string',
        'category_id' => 'nullable|exists:categories,id'
    ]);
    
    // Calcular nuevo checksum del contenido
    $newChecksum = md5($request->content_markdown);
    
    $note->update([
        'title' => $request->title,
        'content_markdown' => $request->content_markdown,
        'content_html' => \Parsedown::instance()->text($request->content_markdown),
        'category_id' => $request->category_id,
        'checksum' => $newChecksum,
        'updated_at' => now(),
    ]);
    
    // Si la nota tiene file_path, actualizar el archivo original (opcional)
    if ($note->file_path && file_exists(dirname($note->file_path))) {
        file_put_contents($note->file_path, $request->content_markdown);
    }
    
    return redirect()->route('notes.show', $note)->with('success', 'Nota actualizada.');
}

    public function filter($categorySlug = null)
    {

        // paginador
        $perPage = request()->input('per_page', 5);

        $user = \App\Models\User::find(Auth::id());
        
        if ($categorySlug) {
            $category = Category::where('slug', $categorySlug)->firstOrFail();
            $totalNotas = $user->notes()->where('category_id', $category->id)->count();
            // $notes = $user->notes()->where('category_id', $category->id)->with('category')->get();
            $notes = $user->notes()->where('category_id', $category->id)->with('category')->paginate($perPage);  // ✅ Correcto
        } else {
            $totalNotas = $user->notes()->count();
            // $notes = $user->notes()->with('category')->get();
            $notes = $user->notes()->with('category')->paginate($perPage);
        }
        
        $totalCategorias = Category::count();
        $categoriasConNotas = Category::withCount('notes')->get();
        
        return view('notes.index', compact('notes', 'totalNotas', 'totalCategorias','categoriasConNotas'));
    }


    public function takeOwnership()
    {
        // Verificar que es administrador
        if (!Auth::user()->is_admin) {
            abort(403, 'No autorizado');
        }
        
        // Reasignar todas las notas al usuario actual
        $total = Note::query()->update(['user_id' => Auth::id()]);
        
        return redirect()->route('notes.index')->with('success', "Se han reasignado {$total} notas a tu usuario.");
    }   

    public function create()
    {
        $categorias = Category::all();
        return view('notes.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content_markdown' => 'required|string',
            'category_id' => 'nullable|exists:categories,id'
        ]);
        
        $slug = Str::slug($request->title) . '-' . uniqid();
        $html = Parsedown::instance()->text($request->content_markdown);
        $checksum = md5($request->content_markdown);
        
        $note = Note::create([
            'title' => $request->title,
            'slug' => $slug,
            'content_markdown' => $request->content_markdown,
            'content_html' => $html,
            'checksum' => $checksum,
            'category_id' => $request->category_id,
            'user_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('notes.show', $note)->with('success', 'Nota creada correctamente.');
    }
}