<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Note;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
// use Illuminate\Container\Attributes\Auth;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
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
        // $perPage = request()->input('per_page', 5); // reemplazado por config desde la BD
        $perPage = UserSetting::getValue(Auth::id(), 'notas_por_pagina', 5);

        // Filtrar las notas por rol
         if (Auth::user()->is_admin) {
        // Administrador: ve todas las notas
        $notes = Note::with('category')->paginate($perPage);
        $totalNotas = Note::count();
        } else {
            // Usuario normal: solo sus notas
            $notes = Note::where('user_id', Auth::id())->with('category')->paginate($perPage);
            $totalNotas = Note::where('user_id', Auth::id())->count();
        }

        //  remove este bloque
        // if (Auth::user()->is_admin) {
        //     // Administrador: ve todas las notas
        //     // $notes = Note::with('category')->get(); // reemplazado, al usar paginacion
        //     $notes = Note::with('category')->paginate($perPage);  // ✅ Correcto
        //     $totalNotas = Note::count(); // Todas las notas
        // } else {
        //     // Usuario normal: solo sus notas
        //     // $notes = Auth::user()->notes()->with('category')->get();
        //     // $notes = Auth::user()->$notes()->with('category')->paginate($perPage);

            
        //     $notes = Note::where('user_id', Auth::id())->with('category')->paginate($perPage);

        //     $totalNotas = Note::count(); // Solo sus notas

        //     $notes = Auth::user();
        //     $notes = Note::with('category')->paginate($perPage);  // ✅ Correcto
        // }
        // remove hasta aqui 


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
        if ($note->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403, 'No autorizado');
        }

        // remove este bloque
        // Filtrar las notas por rol
        // if (Auth::user()->is_admin) {
        //     // Administrador: ve todas las notas
        //     $notes = Note::with('category')->get();
        // } else {
        //     // Usuario normal: solo sus notas
        //     // $notes = Auth::user()->notes()->with('category')->get();
        //     $notes = Auth::user();
        //     $notes()->with('category')->get();
        // }
        // remove 

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

    // REMOVE FROM HERE
    // public function sync()
    // {
    //     // Log para verificar que se ejecuta el botón
    //     Log::info('=== INICIO Sincronización desde web ===');
        
    //     // Ejecutar importación
    //     Artisan::call('notes:import');
    //     $output = Artisan::output();
        
    //     Log::info('Salida de notes:import:');
    //     Log::info($output);
        
    //     // Actualizar checksums de notas que cambiaron en disco
    //     $notes = Note::whereNotNull('file_path')->get();
    //     $updated = 0;
        
    //     Log::info('Notas con file_path: ' . $notes->count());
        
    //     foreach ($notes as $note) {
    //         if (file_exists($note->file_path)) {
    //             $currentChecksum = md5(file_get_contents($note->file_path));
    //             if ($currentChecksum !== $note->checksum) {
    //                 $note->checksum = $currentChecksum;
    //                 $note->save();
    //                 $updated++;
    //                 Log::info('Checksum actualizado para nota ID: ' . $note->id);
    //             }
    //         } else {
    //             Log::warning('Archivo no encontrado: ' . $note->file_path);
    //         }
    //     }
        
    //     $message = "Notas sincronizadas correctamente.";
    //     if ($updated > 0) {
    //         $message .= " ($updated notas actualizadas por cambios en disco)";
    //     }
        
    //     Log::info('=== FIN Sincronización ===');
        
    //     return redirect()->route('notes.index')->with('success', $message);
    // }
    // REMOVE UNTIL HERE 

    public function sync()
    {
        $userId = Auth::id();
        // $directorioConfigurado = UserSetting::getValue($userId, 'directorio_notas', base_path('notes'));
        $directorioConfigurado = UserSetting::getValue($userId, 'directorio_notas', base_path('public/notes'));
        
        // Verificar si el directorio existe
        if (!File::exists($directorioConfigurado)) {
            // Intentar crear el directorio automáticamente
            try {
                File::makeDirectory($directorioConfigurado, 0755, true);
                $mensaje = "📁 Directorio creado automáticamente: {$directorioConfigurado}\n\n";
                $mensaje .= "Ahora debes copiar tus archivos .md a este directorio.\n\n";
                $mensaje .= "Instrucciones:\n";
                $mensaje .= "1. Abre otra terminal\n";
                $mensaje .= "2. Ejecuta: podman cp ~/notes/. dk-app:{$directorioConfigurado}/\n";
                $mensaje .= "     podman cp ~/notes/. dk-app:{$directorioConfigurado}/\n";
                $mensaje .= "3. Luego vuelve a hacer clic en Sincronizar";
                
                return redirect()->route('notes.index')->with('warning', $mensaje);
            } catch (\Exception $e) {
                // No se pudo crear automáticamente, mostrar instrucciones manuales
                $mensaje = "❌ El directorio no existe y no se pudo crear automáticamente.\n\n";
                $mensaje .= "Configuración actual: {$directorioConfigurado}\n\n";
                $mensaje .= "Para solucionar, ejecuta manualmente:\n";
                $mensaje .= "docker exec -it dk-app mkdir -p {$directorioConfigurado}\n";
                $mensaje .= "docker exec -it dk-app chmod 755 {$directorioConfigurado}\n\n";
                $mensaje .= "Luego copia tus notas:\n";
                $mensaje .= "docker cp ~/notes/. dk-app:{$directorioConfigurado}/\n\n";
                $mensaje .= "Después vuelve a intentar Sincronizar";
                
                return redirect()->route('notes.index')->with('error', $mensaje);
            }
        }
        
        // Verificar si el directorio está vacío
        $archivos = File::allFiles($directorioConfigurado);
        $archivosMd = array_filter($archivos, function($file) {
            return in_array($file->getExtension(), ['md', 'markdown']);
        });
        
        if (count($archivosMd) === 0) {
            $mensaje = "⚠️ El directorio existe pero está vacío.\n\n";
            $mensaje = "No se encontraron archivos .md en: {$directorioConfigurado}\n\n";
            $mensaje .= "Copia tus notas al directorio y vuelve a intentar:\n";
            $mensaje .= "docker cp ~/notes/. dk-app:{$directorioConfigurado}/";
            
            return redirect()->route('notes.index')->with('warning', $mensaje);
        }
        
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
        
        $message = "✅ Notas sincronizadas correctamente.";
        if ($updated > 0) {
            $message .= " ($updated notas actualizadas por cambios en disco)";
        }
        $message .= "\n\n📁 Directorio: {$directorioConfigurado}";
        
        Log::info('=== FIN Sincronización ===');
        
        return redirect()->route('notes.index')->with('success', $message);
    }


    public function destroy(Note $note)
    {
        if ($note->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        // remove este bloque 
        // // Filtrar las notas por rol
        // if (Auth::user()->is_admin) {
        //     // Administrador: ve todas las notas
        //     $notes = Note::with('category')->get();
        // } else {
        //     // Usuario normal: solo sus notas
        //     // $notes = Auth::user()->notes()->with('category')->get();
        //     $notes = Auth::user();
        //     $notes()->with('category')->get();
        // }
        // remove hasta aqui 
        
        $note->delete();
        return redirect()->route('notes.index')->with('success', 'Nota eliminada.');
    }

     public function edit(Note $note)
    {
        if ($note->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        // remove este bloque 
        // Filtrar las notas por rol
        // if (Auth::user()->is_admin) {
        //     // Administrador: ve todas las notas
        //     $notes = Note::with('category')->get();
        // } else {
        //     // Usuario normal: solo sus notas
        //     // $notes = Auth::user()->notes()->with('category')->get();
        //     $notes = Auth::user();
        //     $notes()->with('category')->get();
        // }
        // remove hasta aqui
        
        $categoriasConNotas = Category::withCount('notes')->get();

        return view('notes.edit', compact('note','categoriasConNotas'));
    }


    public function update(Request $request, Note $note)
    {
        if ($note->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        // remove este bloque 
        // Filtrar las notas por rol
        // if (Auth::user()->is_admin) {
        //     // Administrador: ve todas las notas
        //     $notes = Note::with('category')->get();
        // } else {
        //     // Usuario normal: solo sus notas
        //     // $notes = Auth::user()->notes()->with('category')->get();
        //     $notes = Auth::user();
        //     $notes()->with('category')->get();
        // }
        // remove hasta aqui 

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
        // $perPage = request()->input('per_page', 5); // Configurable desde la BD
        $perPage = UserSetting::getValue(Auth::id(), 'notas_por_pagina', 5);

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

         // Procesar categoría: asignar "General" por defecto si no hay
        $categoryId = $request->category_id;
        if (empty($categoryId)) {
            $defaultCategory = Category::firstOrCreate(
                ['slug' => 'general'],
                ['name' => 'General']
            );
            $categoryId = $defaultCategory->id;
        }
        
        $slug = Str::slug($request->title) . '-' . uniqid();
        $html = Parsedown::instance()->text($request->content_markdown);
        $checksum = md5($request->content_markdown);
        
        $note = Note::create([
            'title' => $request->title,
            'slug' => $slug,
            'content_markdown' => $request->content_markdown,
            'content_html' => $html,
            'checksum' => $checksum,
            // 'category_id' => $request->category_id,
            'category_id' => $categoryId,
            'user_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('notes.show', $note)->with('success', 'Nota creada correctamente.');
    }

    // BEGIN 
    // public function upload(Request $request)
    // {

    //     //  dd($request->all()); // ← Agrega esta línea al principio

    //     $request->validate([
    //         'file' => 'required|file|mimes:md,markdown|max:2048', // máx 2MB
    //         'category_id' => 'nullable|string',
    //         'new_category' => 'nullable|string|max:100'
    //     ]);

    //      dd('1. Validación pasó'); // ← Punto 1

    //     // AGREGAR LOGS AQUÍ
    //     Log::info('=== SUBIR NOTA ===');
    //     Log::info('Category ID recibido: ' . $request->category_id);
    //     Log::info('New category: ' . $request->new_category);
    
    //     // Procesar categoría
    //     $categoryId = null;


    //     if ($request->category_id == 'new' && $request->new_category) {
    //         // Crear nueva categoría
    //         Log::info('Creando nueva categoría: ' . $request->new_category);
    //         $slug = Str::slug($request->new_category);
    //         $category = Category::firstOrCreate(
    //             ['slug' => $slug],
    //             ['name' => $request->new_category]
    //         );
    //         $categoryId = $category->id;
    //         Log::info('Categoría creada con ID: ' . $categoryId);
    //     } elseif (is_numeric($request->category_id) && $request->category_id > 0) {
    //         // Usar categoría existente
    //         Log::info('Usando categoría existente ID: ' . $categoryId);
    //         $categoryId = (int) $request->category_id;
    //     }

    //     //   dd('2. Categoría procesada: ' . $categoryId); // ← Punto 2
        
    //     Log::info('Category ID final: ' . $categoryId);

    //     // Leer el archivo subido
    //     $file = $request->file('file');
    //     $contenido = File::get($file->getPathname());
    //     $nombreArchivo = $file->getClientOriginalName();
        
    //     //    dd('3. Archivo leído: ' . strlen($contenido) . ' bytes'); // ← Punto 3

    //     // Extraer título
    //     $titulo = $this->extraerTitulo($contenido, $nombreArchivo);
        
    //     // Convertir Markdown a HTML
    //     $parsedown = new Parsedown();
    //     $html = $parsedown->text($contenido);
        
    //     // Calcular checksum
    //     $checksum = md5($contenido);
        
    //     // Verificar duplicado por checksum
    //     $existente = Note::where('checksum', $checksum)->first();
    //     if ($existente) {
    //         return redirect()->route('notes.index')->with('warning', 'La nota ya existe: ' . $existente->title);
    //     }
        
    //     // Crear slug único
    //     $slug = Str::slug($titulo) . '-' . uniqid();
        
    //     // Guardar nota
    //     $note = Note::create([
    //         'title' => $titulo,
    //         'slug' => $slug,
    //         'content_markdown' => $contenido,
    //         'content_html' => $html,
    //         'checksum' => $checksum,
    //         'category_id' => $categoryId ?: null,
    //         'user_id' => Auth::id(),
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);

    //     Log::info('Nota guardada con ID: ' . ($note->id ?? 'no creada'));
        
    //     return redirect()->route('notes.show', $note)->with('success', 'Nota subida correctamente.');
    // }
    // END  

    public function upload(Request $request)
{
    try {

        // // ✅ AGREGAR ESTO AL PRINCIPIO
        // $file = $request->file('file');
        // dd([
        // 'nombre' => $file->getClientOriginalName(),
        // 'extension' => $file->getClientOriginalExtension(),
        // 'mime' => $file->getMimeType(),
        // 'tamaño' => $file->getSize(),
        // ]);

        // Punto 1: Validación del archivo .md a subir
        $request->validate([
            'file' => 'required|file|max:20480',
            'category_id' => 'nullable|string',
            'new_category' => 'nullable|string|max:100'
            ]);
            
            // 'file' => 'required|file|mimes:md,markdown,txt,text/plain|max:2048',
        // 'file' => 'required|file|mimes:md,markdown|max:2048',


        // Punto 2: Procesar categoría
        $categoryId = null;
        
        // begin bloque a remover 
        // if ($request->category_id == 'new' && $request->new_category) {
        //     $slug = Str::slug($request->new_category);
        //     $category = Category::firstOrCreate(
        //         ['slug' => $slug],
        //         ['name' => $request->new_category]
        //     );
        //     $categoryId = $category->id;
        // } elseif (is_numeric($request->category_id) && $request->category_id > 0) {
        //     $categoryId = (int) $request->category_id;
        // }
        // END remove si funciona 
        // Procesar categoría
        // $categoryId = null;

        // remove desde aqui
        // Si se proporcionó una nueva categoría (texto), úsala primero
        // if ($request->new_category && trim($request->new_category) !== '') {
        //     $slug = \Str::slug($request->new_category);
        //     $category = Category::firstOrCreate(
        //         ['slug' => $slug],
        //         ['name' => $request->new_category]
        //     );
        //     $categoryId = $category->id;
        // } 
        // // Si no, usar la categoría seleccionada en el select (si es numérica)
        // elseif (is_numeric($request->category_id) && $request->category_id > 0) {
        //     $categoryId = (int) $request->category_id;
        // }
        // remover 
      
        // 1. Si se escribió nueva categoría (texto)
        if ($request->new_category && trim($request->new_category) !== '') {
            $slug = Str::slug($request->new_category);
            $category = Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => trim($request->new_category)]
            );
            $categoryId = $category->id;
        } 
        // 2. Si se seleccionó una categoría existente (ID numérico)
        elseif (is_numeric($request->category_id) && $request->category_id > 0) {
            $categoryId = (int) $request->category_id;
        }
        // 3. Si no hay categoría, asignar "General" por defecto
        else {
            $defaultCategory = Category::firstOrCreate(
                ['slug' => 'general'],
                ['name' => 'General']
            );
            $categoryId = $defaultCategory->id;
        }
        
        // Punto 3: Leer archivo
        $file = $request->file('file');
        $contenido = File::get($file->getPathname());
        $nombreArchivo = $file->getClientOriginalName();
        
        // Punto 4: Extraer título
        $titulo = $this->extraerTitulo($contenido, $nombreArchivo);

        // Limitar a 255 caracteres
        $titulo = substr($titulo, 0, 250);
        
        // Punto 5: Convertir Markdown a HTML
        $parsedown = new Parsedown();
        $html = $parsedown->text($contenido);
        
        // Punto 6: Calcular checksum
        $checksum = md5($contenido);
        
        // Punto 7: Verificar duplicado
        $existente = Note::where('checksum', $checksum)->first();
        if ($existente) {
            return redirect()->route('notes.index')->with('error', 'La nota ya existe: ' . $existente->title);
        }
        
        // Punto 8: Crear slug
        $slug = Str::slug($titulo) . '-' . uniqid();
        
        // Punto 9: Guardar nota
        $note = Note::create([
            'title' => $titulo,
            'slug' => $slug,
            'content_markdown' => $contenido,
            'content_html' => $html,
            'checksum' => $checksum,
            'category_id' => $categoryId,
            'user_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('notes.show', $note)->with('success', '✅ Nota subida correctamente. ID: ' . $note->id);
        
    } catch (\Exception $e) {
        // Capturar cualquier error y mostrarlo en la página de notas
        return redirect()->route('notes.index')->with('error', '❌ Error al subir: ' . $e->getMessage() . ' - Línea: ' . $e->getLine());
    }
}

    // Reutilizar el método extraerTitulo (si no existe, créalo)
    private function extraerTitulo(string $contenido, string $nombreArchivo): string
    {

        //   dd('4. Título: ' . $titulo); // ← Punto 4

        $lineas = explode("\n", $contenido);
        foreach ($lineas as $linea) {
            if (str_starts_with(trim($linea), '# ')) {
                return trim(substr(trim($linea), 2));
            }
        }
        return pathinfo($nombreArchivo, PATHINFO_FILENAME);
    }

    // Elimina toda las notas 
    public function deleteAll()
    {
        // Verificar que es administrador
        if (!Auth::user()->is_admin) {
            abort(403, 'No autorizado. Solo administradores pueden eliminar todas las notas.');
        }
        
        // Contar notas antes de eliminar
        $count = Note::count();
        
        // Eliminar todas las notas
        Note::truncate();
        
        // Mensaje de éxito
        $message = "Se han eliminado {$count} notas permanentemente.";
        
        return redirect()->route('settings.index')->with('success', $message);
    }
}