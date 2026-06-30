<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Note;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Parsedown;

class NoteController extends Controller
{
    /**
     * Lista todas las notas del usuario autenticado
     */
    public function index()
    {

        
        // 1. Obtener la paginación configurada por el usuario desde la base de datos
        $perPage = UserSetting::getValue(Auth::id(), 'notas_por_pagina', 5);
        
        // 2. REGLA DE PRIVACIDAD UNIFICADA: Tanto admin como usuarios normales solo ven SUS PROPIAS notas
        $notes = Note::where('user_id', Auth::id())
        ->with(['category', 'tags']) // Eager loading optimizado para los badges
        ->latest()
        ->paginate($perPage);
        
        // 3. Totales para las dos primeras tarjetas de estadísticas
        $totalNotas = Note::where('user_id', Auth::id())->count();
        // $totalCategorias = Category::count();
        $totalCategorias = Category::where('user_id', Auth::id())->count();
        // $totalCategorias = auth()->user()->categories->count(); // ✅ Contar solo las categorías del usuario autenticado
      $categories = auth()->user()->categories;

        // 4. Cargar todas las etiquetas válidas contando solo las notas de este usuario
        $tagsWithCount = Tag::whereNull('user_id')
            ->orWhere('user_id', Auth::id())
            ->withCount(['notes' => function ($query) {
                $query->where('notes.user_id', Auth::id());
            }])
            ->orderBy('name')
            ->get();

        // Guardar la página actual en sesión para volver después de editar/crear
        session(['last_notes_page' => request()->input('page', 1)]);
        session()->forget('last_notes_filter');

        // 5. Retornar la vista pasando todas las variables limpias
        return view('notes.index', compact('notes', 'totalNotas', 'totalCategorias', 'tagsWithCount'));

    }

    private function getNoteStatus(Note $note)
    {
        // Sin categoría
        if (is_null($note->category_id)) {
            return 'uncategorized';
        }

        // Categoría huérfana (la categoría fue eliminada)
        if ($note->category && ! $note->category->exists) {
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
        if ($note->user_id !== Auth::id() && ! Auth::user()->is_admin) {
            abort(403, 'No autorizado');
        }

        // Conteo de notas por categorias
        $categoriasConNotas = Category::withCount('notes')->get();

        // para no perder la ubicacion del paginador
        $page = request()->input('page', 1);

        // Retornar vista con la nota
        return view('notes.show', compact('note', 'categoriasConNotas', 'page'));
    }

    public function sync()
    {
        $userId = Auth::id();

        if (! $userId) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión.');
        }

        // Obtener ruta personal y construir ruta completa
        $rutaPersonal = UserSetting::getValue($userId, 'ruta_personal', '');
        $directorioBase = base_path('storage/app/public/notes');

        if (! $rutaPersonal) {
            return redirect()->route('notes.index')->with('error', '❌ No has configurado tu ruta personal. Ve a Configuración ⚙️');
        }

        $directorioConfigurado = $directorioBase.'/'.$rutaPersonal;

        // Verificar que el directorio existe
        if (! File::exists($directorioConfigurado)) {
            // Intentar crear el directorio automáticamente
            try {
                File::makeDirectory($directorioConfigurado, 0755, true);
                $mensaje = "📁 Directorio creado automáticamente: {$directorioConfigurado}\n\n";
                $mensaje .= "Ahora debes copiar tus archivos .md a este directorio.\n\n";
                $mensaje .= "Instrucciones:\n";
                $mensaje .= "1. Abre otra terminal\n";
                $mensaje .= "2. Ejecuta: podman cp ~/notes/. dk-app:{$directorioConfigurado}/\n";
                $mensaje .= '3. Luego vuelve a hacer clic en Sincronizar';

                return redirect()->route('notes.index')->with('warning', $mensaje);
            } catch (\Exception $e) {
                $mensaje = "❌ El directorio no existe y no se pudo crear automáticamente.\n\n";
                $mensaje .= "Ruta esperada: {$directorioConfigurado}\n\n";
                $mensaje .= "Ejecuta manualmente:\n";
                $mensaje .= "podman exec -it dk-app mkdir -p {$directorioConfigurado}\n";
                $mensaje .= "podman exec -it dk-app chmod 755 {$directorioConfigurado}\n\n";
                $mensaje .= "Luego copia tus notas:\n";
                $mensaje .= "podman cp ~/notes/. dk-app:{$directorioConfigurado}/\n\n";
                $mensaje .= 'Después vuelve a intentar Sincronizar';

                return redirect()->route('notes.index')->with('error', $mensaje);
            }
        }

        // Verificar si el directorio tiene archivos .md
        $archivos = File::allFiles($directorioConfigurado);
        $archivosMd = array_filter($archivos, function ($file) {
            return in_array($file->getExtension(), ['md', 'markdown']);
        });

        if (count($archivosMd) === 0) {
            $mensaje = "⚠️ El directorio existe pero está vacío.\n\n";
            $mensaje .= "No se encontraron archivos .md en: {$directorioConfigurado}\n\n";
            $mensaje .= "Copia tus notas al directorio:\n";
            $mensaje .= "podman cp ~/notes/. dk-app:{$directorioConfigurado}/";

            return redirect()->route('notes.index')->with('warning', $mensaje);
        }

        // Log para verificar que se ejecuta el botón
        Log::info('=== INICIO Sincronización desde web ===');
        Log::info('Directorio: '.$directorioConfigurado);

        // Ejecutar importación pasando el ID del usuario actual
        // Artisan::call('notes:import', ['--user' => Auth::id()]);

        // Usamos la variable $userId ya definida arriba
        Artisan::call('notes:import', ['--user' => $userId]);
        $output = Artisan::output();

        Log::info('Salida de notes:import:');
        Log::info($output);

        // Actualizar checksums de notas que cambiaron en disco
        // $notes = Note::whereNotNull('file_path')->get();

        // 🔍 AJUSTE CRÍTICO: Filtrar notas SOLO del usuario autenticado
        $notes = Note::where('user_id', $userId)
            ->whereNotNull('file_path')
            ->get();

        $updated = 0;

        foreach ($notes as $note) {
            if (file_exists($note->file_path)) {
                $currentChecksum = md5(file_get_contents($note->file_path));
                if ($currentChecksum !== $note->checksum) {
                    $note->checksum = $currentChecksum;
                    $note->save();
                    $updated++;
                    Log::info('Checksum actualizado para nota ID: '.$note->id);
                }
            } else {
                Log::warning('Archivo no encontrado: '.$note->file_path);
            }
        }

        $message = '✅ Notas sincronizadas correctamente.';
        if ($updated > 0) {
            $message .= " ($updated notas actualizadas por cambios en disco)";
        }
        $message .= "\n\n📁 Directorio: {$directorioConfigurado}";

        Log::info('=== FIN Sincronización ===');

        return redirect()->route('notes.index')->with('success', $message);
    }

    public function destroy(Note $note)
    {
        if ($note->user_id !== Auth::id() && ! Auth::user()->is_admin) {
            abort(403);
        }

        $page = session('last_notes_page', 1);
        $filter = session('last_notes_filter');

        $note->delete();

        if ($filter) {
            return redirect()->route('notes.filter', ['category' => $filter, 'page' => $page])->with('success', 'Nota eliminada.');
        } else {
            return redirect()->route('notes.index', ['page' => $page])->with('success', 'Nota eliminada.');
        }

        // return redirect()->route('notes.index', ['page' => $page])->with('success', 'Nota eliminada.');
    }

    public function edit(Note $note)
    {
        if ($note->user_id !== Auth::id() && ! Auth::user()->is_admin) {
            abort(403);
        }

        $page = request()->input('page', 1);

        $categoriasConNotas = Category::withCount('notes')->get();

        return view('notes.edit', compact('note', 'categoriasConNotas', 'page'));
    }

    public function update(Request $request, Note $note)
    {
        if ($note->user_id !== Auth::id() && ! Auth::user()->is_admin) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content_markdown' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        // Calcular nuevo checksum del contenido
        $newChecksum = md5($request->content_markdown);

        $note->update([
            'title' => $request->title,
            'content_markdown' => $request->content_markdown,
            'content_html' => Parsedown::instance()->text($request->content_markdown),
            'category_id' => $request->category_id,
            'checksum' => $newChecksum,
            'updated_at' => now(),
        ]);

        // Si la nota tiene file_path, actualizar el archivo original (opcional)
        if ($note->file_path && file_exists(dirname($note->file_path))) {
            file_put_contents($note->file_path, $request->content_markdown);
        }

        // Obtener la página guardada (o 1 si no existe)
        $page = session('last_notes_page', 1);
        $filter = session('last_notes_filter');

        if ($filter) {
            return redirect()->route('notes.filter', ['category' => $filter, 'page' => $page])->with('success', 'Nota actualizada.');
        } else {
            return redirect()->route('notes.index', ['page' => $page])->with('success', 'Nota actualizada.');
        }

        // return redirect()->route('notes.show', ['note' => $note, 'page' => $page])->with('success', 'Nota actualizada.');
    }

    public function filter($categorySlug = null)
    {

        // paginador
        // $perPage = request()->input('per_page', 5); // Configurable desde la BD
        $perPage = UserSetting::getValue(Auth::id(), 'notas_por_pagina', 5);

        $user = User::find(Auth::id());

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

        // $totalCategorias = Category::count();
        $totalCategorias = Category::where('user_id', Auth::id())->count();
        $categoriasConNotas = Category::withCount('notes')->get();

        // 🚀 NUEVO: Cargar todas las etiquetas válidas para que la cabecera no se rompa al filtrar categorías
        $tagsWithCount = Tag::whereNull('user_id')
            ->orWhere('user_id', Auth::id())
            ->withCount(['notes' => function ($query) {
                $query->where('notes.user_id', Auth::id());
            }])
            ->orderBy('name')
            ->get();

        // Guardar página y filtro actual en sesión
        session(['last_notes_page' => request()->input('page', 1)]);
        session(['last_notes_filter' => $categorySlug]);

        return view('notes.index', compact('notes', 'totalNotas', 'totalCategorias', 'categoriasConNotas', 'tagsWithCount'));
    }

    // public function takeOwnership()
    // {
    //     // Verificar que es administrador
    //     // if (! Auth::user()->is_admin) {
    //     //     abort(403, 'No autorizado');
    //     // } Todos los usuarios pueden reasignar notas a sí mismos

    //     // Reasignar todas las notas al usuario actual
    //     $total = Note::query()->update(['user_id' => Auth::id()]);

    //     return redirect()->route('notes.index')->with('success', "Se han reasignado {$total} notas a tu usuario.");
    // }
    // public function takeOwnership()
    // {
    //     $userId = Auth::id();

    //     if (! $userId) {
    //         return redirect()->route('login')->with('error', 'Debes iniciar sesión.');
    //     }

    //     // 1. Obtener la ruta personal del usuario desde las configuraciones
    //     $rutaPersonal = UserSetting::getValue($userId, 'ruta_personal', '');

    //     if (! $rutaPersonal) {
    //         $mensajeError = 'No tienes una ruta personal configurada para identificar tus notas. Ve a Configuración.';
    //         return redirect()->route('notes.index')->with('error', $mensajeError);
    //     }

    //     // 2. Construir el prefijo de la ruta absoluta en el disco
    //     $directorioBase = base_path('storage/app/public/notes/' . $rutaPersonal);
    //     if (! $directorioBase) {
    //         return redirect()->route('notes.index')->with('error', '❌ No ha sido configurada su ruta personal. Vaya a Configuración ⚙️');
    //     }

    //     // 3. Actualización segura: Solo se reasignan las notas cuyo 'file_path' comience con su directorio
    //     $total = Note::where('file_path', 'like', $directorioBase . '%')
    //         ->update(['user_id' => $userId]);

    //     // 4. Retornar con un mensaje de éxito basado en los registros modificados
    //     if ($total > 0) {
    //         $mensajeExito = 'Se han reasignado ' . $total . ' notas de tu directorio personal a tu cuenta de usuario.';
    //         return redirect()->route('notes.index')->with('success', $mensajeExito);
    //     }

    //     $mensajeInfo = 'No se encontraron notas pendientes de asignación en tu directorio personal.';
    //     return redirect()->route('notes.index')->with('info', $mensajeInfo);
    // }

    public function takeOwnership()
    {
        $userId = Auth::id();

        if (! $userId) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión.');
        }

        $rutaPersonal = UserSetting::getValue($userId, 'ruta_personal', '');

        $directorioBase = base_path('storage/app/public/notes/'.$rutaPersonal);

        if (! $rutaPersonal) {
            $mensajeError = 'No tiene un directorio personal configurado. Vaya a Configuración.';

            return redirect()->route('notes.index')->with('error', $mensajeError);
        }

        // 3. Actualización segura en la Base de Datos usando la ruta absoluta
        $total = Note::where('file_path', 'like', $directorioBase.'%')
            ->update(['user_id' => $userId]);

        // 4. Retornar con una respuesta amigable y orientativa
        if ($total > 0) {
            $mensajeExito = 'Se han reasignado '.$total.' notas a su cuenta de usuario.';

            return redirect()->route('notes.index')->with('success', $mensajeExito);
        }

        // ORIENTACIÓN AL USUARIO: Validamos la existencia real usando la ruta absoluta completa
        if (! \File::exists($directorioBase)) {
            $mensajeGuia = 'No encontramos notas asignadas. Tu carpeta personal  aún no ha sido creada o está vacía. Te recomendamos Sincronizar para escanear tus archivos primero.';

            return redirect()->route('notes.index')->with('warning', $mensajeGuia);
        }

        // Si la carpeta absoluta existe pero realmente no había notas de otros dueños en la BD
        $mensajeInfo = 'Tu directorio ya está al día. Todas las notas ya pertenecen a tu cuenta.';

        return redirect()->route('notes.index')->with('info', $mensajeInfo);
    }

    public function create()
    {
        $categorias = Category::all();

        return view('notes.create', compact('categorias'));
    }

    public function store(Request $request)
    {

        $page = session('last_notes_page', 1);
        $filter = session('last_notes_filter');

        $request->validate([
            'title' => 'required|string|max:255',
            'content_markdown' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
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

        $slug = Str::slug($request->title).'-'.uniqid();
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

        if ($filter) {
            return redirect()->route('notes.filter', ['category' => $filter, 'page' => $page])->with('success', 'Nota creada correctamente.');
        } else {
            return redirect()->route('notes.index', ['page' => $page])->with('success', 'Nota creada correctamente.');
        }

        // return redirect()->route('notes.show', ['note' => $note, 'page' => $page])->with('success', 'Nota creada correctamente.');
    }

    public function upload(Request $request)
    {
        try {

            // Punto 1: Validación del archivo .md a subir
            $request->validate([
                'file' => 'required|file|max:20480',
                'category_id' => 'nullable|string',
                'new_category' => 'nullable|string|max:100',
            ]);

            // 'file' => 'required|file|mimes:md,markdown,txt,text/plain|max:2048',
            // 'file' => 'required|file|mimes:md,markdown|max:2048',

            // Punto 2: Procesar categoría
            $categoryId = null;

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

            // Punto 5: Convertir Markdown a HTML y mantiene espaciado entre lineas
            $parsedown = new Parsedown;
            // $parsedown->setBreaksEnabled(true);
            $html = $parsedown->text($contenido);

            // Punto 6: Calcular checksum
            $checksum = md5($contenido);

            // Punto 7: Verificar duplicado
            $existente = Note::where('checksum', $checksum)->first();
            if ($existente) {
                return redirect()->route('notes.index')->with('error', 'La nota ya existe: '.$existente->title);
            }

            // Punto 8: Crear slug
            $slug = Str::slug($titulo).'-'.uniqid();

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
            $page = request()->input('page', 1);

            return redirect()->route('notes.show', ['note' => $note, 'page' => $page])->with('success', '✅ Nota subida correctamente. ID: '.$note->id);
            // return redirect()->route('notes.show', $note)->with('success', '✅ Nota subida correctamente. ID: ' . $note->id);

        } catch (\Exception $e) {
            // Capturar cualquier error y mostrarlo en la página de notas
            return redirect()->route('notes.index')->with('error', '❌ Error al subir: '.$e->getMessage().' - Línea: '.$e->getLine());
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

    // REMOVE
    // Elimina toda las notas
    // public function deleteAll()
    // {
    //     // Verificar que es administrador
    //     if (! Auth::user()->is_admin) {
    //         abort(403, 'No autorizado. Solo administradores pueden eliminar todas las notas.');
    //     }

    //     // Contar notas antes de eliminar
    //     $count = Note::count();

    //     // Eliminar todas las notas
    //     Note::truncate();

    //     // Mensaje de éxito
    //     $message = "Se han eliminado {$count} notas permanentemente.";

    //     return redirect()->route('settings.index')->with('success', $message);
    // }
    // REMOVE

    // Elimina todos los registros de un usuario (Notas, Categorías, Etiquetas)
    public function deleteAll()
    {
        $userId = auth()->id();

        // Ejecutamos en una transacción para asegurar que se borre todo o nada
        DB::transaction(function () use ($userId, &$count) {
            // 1. Contar y eliminar Notas del usuario
            $count = Note::where('user_id', $userId)->count();
            Note::where('user_id', $userId)->delete();

            // 2. Eliminar Categorías del usuario
            Category::where('user_id', $userId)->delete();

            // 3. Eliminar Etiquetas del usuario
            Tag::where('user_id', $userId)->delete();
        });

        // Mensaje de éxito
        $message = "Se han eliminado permanentemente tus {$count} notas y sus datos asociados.";

        return redirect()->route('settings.index')->with('success', $message);
    }

    // public function filterByTag($tagSlug)
    // {
    //     // Buscamos la etiqueta por su slug asegurando la privacidad (Nativa o del usuario)
    //     $tag =  Tag::where('slug', $tagSlug)
    //         ->where(function($query) {
    //             $query->whereNull('user_id')
    //                 ->orWhere('user_id', Auth::id());
    //         })->firstOrFail();

    //     // Traemos solo las notas del usuario autenticado que tengan vinculada esa etiqueta
    //     $notes = Note::where('user_id', Auth::id())
    //         ->whereHas('tags', function($query) use ($tag) {
    //             $query->where('tags.id', $tag->id);
    //         })
    //         ->with('category', 'tags') // Eager loading para optimizar queries
    //         ->latest()
    //         ->paginate(10); // Ajusta según la paginación de tu app

    //     $tagsWithCount = Tag::whereNull('user_id')
    //         ->orWhere('user_id', Auth::id())
    //         ->withCount(['notes' => function($query) {
    //             $query->where('notes.user_id', Auth::id());
    //         }])
    //         ->orderBy('name')
    //         ->get();

    //     // Mantenemos la consistencia con las variables estadísticas de tu index actual
    //     $totalNotas = Note::where('user_id', Auth::id())->count();
    //     $totalCategorias = Category::count();

    //     return view('notes.index', compact('notes', 'totalNotas', 'totalCategorias','tagsWithCount'));
    // }

    /**
     * Filtrar las notas por una etiqueta específica (Nativa o Personal)
     *
     * @param  string  $tagSlug
     * @return View
     */
    public function filterByTag($tagSlug)
    {

        // 🚀 NUEVO: Obtener la paginación configurada por el usuario
        $perPage = UserSetting::getValue(Auth::id(), 'notas_por_pagina', 5);

        // 1. Buscar la etiqueta por su slug garantizando el aislamiento de privacidad
        // Un usuario solo puede usar una etiqueta si es nativa (user_id NULL) o si él la creó
        $tag = Tag::where('slug', $tagSlug)
            ->where(function ($query) {
                $query->whereNull('user_id')
                    ->orWhere('user_id', Auth::id());
            })->firstOrFail();

        // 2. Traer solo las notas pertenecientes al usuario actual que tengan vinculada esta etiqueta
        $notes = Note::where('user_id', Auth::id())
            ->whereHas('tags', function ($query) use ($tag) {
                $query->where('tags.id', $tag->id);
            })
            ->with(['category', 'tags']) // Eager loading para evitar problemas de N+1 queries en los badges
            ->latest()
            ->paginate($perPage); // 🚀 CAMBIADO: Usar $perPage en vez de 10 estático

        // ->paginate(10); // Mantén el mismo número de paginación que tu index

        // 3. Recalcular las estadísticas básicas para las dos primeras tarjetas
        $totalNotas = Note::where('user_id', Auth::id())->count();
        // $totalCategorias = Category::count();
        $totalCategorias = Category::where('user_id', Auth::id())->count();

        // 4. Cargar todas las etiquetas visibles para el usuario con el conteo de notas en tiempo real
        // Filtrado estrictamente para contar solo las notas del usuario autenticado
        $tagsWithCount = Tag::whereNull('user_id')
            ->orWhere('user_id', Auth::id())
            ->withCount(['notes' => function ($query) {
                $query->where('notes.user_id', Auth::id());
            }])
            ->orderBy('name')
            ->get();

        // 5. Retornar la vista con todas las variables necesarias
        return view('notes.index', compact('notes', 'totalNotas', 'totalCategorias', 'tagsWithCount'));
    }
}
