
# Componentes de controladores de Notas



## app/Http/Controllers/NoteController.php

```php
<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Note;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Container\Attributes\Auth;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

        // Guardar la página actual en sesión para volver después de editar/crear
        session(['last_notes_page' => request()->input('page', 1)]);
        session(['last_notes_filter' => null]);

        // total categorias
        $totalCategorias = Category::count();

        // Forzar la variable aquí
        $categoriasConNotas = Category::withCount('notes')->get();

        // Detectar estado de cada nota
        foreach ($notes as $note) {
            $note->status = $this->getNoteStatus($note);
        }

        // Retornar vista con las notas
        // return view('notes.index', compact('notes'));
        return view('notes.index', compact('notes', 'totalNotas', 'totalCategorias', 'categoriasConNotas'));

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
        Artisan::call('notes:import', ['--user' => Auth::id()]);
        $output = Artisan::output();


        Log::info('Salida de notes:import:');
        Log::info($output);

        // Actualizar checksums de notas que cambiaron en disco
        $notes = Note::whereNotNull('file_path')->get();
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

        $totalCategorias = Category::count();
        $categoriasConNotas = Category::withCount('notes')->get();

        // Guardar página y filtro actual en sesión
        session(['last_notes_page' => request()->input('page', 1)]);
        session(['last_notes_filter' => $categorySlug]);

        return view('notes.index', compact('notes', 'totalNotas', 'totalCategorias', 'categoriasConNotas'));
    }

    public function takeOwnership()
    {
        // Verificar que es administrador
        if (! Auth::user()->is_admin) {
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

    // Elimina toda las notas
    public function deleteAll()
    {
        // Verificar que es administrador
        if (! Auth::user()->is_admin) {
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
```

**Linea asociada al error:**

En NoteController.php

```php
    Artisan::call('notes:import', ['--user' => Auth::id()]);
````




## app/Console/Commands/ImportNotesCommand.php

```php
<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Note;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
// as FacadesFile;

use Parsedown;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Attribute\AsCommand; // 🌟 Este es el namespace real de Symfony que usa Laravel 12 de fondo

// #[Signature('notes:import')]
// #[Signature('notes:import {--user=}')]
// #[Description('Importa notas Markdown desde un directorio')]
#[AsCommand(name: 'notes:import', description: 'Importar notas Markdown desde un directorio')]

class ImportNotesCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {

        // $usuario = User::first();
        // if (!$usuario) {
        //     $this->error("❌ No hay usuarios en el sistema.");
        // return 1;
        // }

        $userId = $this->option('user');
        if ($userId) {
            $usuario = User::find($userId);
        } else {
            $usuario = User::first();
        }

        if (! $usuario) {
            $this->error('❌ No hay usuarios en el sistema.');

            return 1;
        }

        // Obtener ruta personal y construir ruta completa
        $rutaPersonal = UserSetting::getValue($usuario->id, 'ruta_personal', '');
        $directorioBase = base_path('storage/app/public/notes');
        // $directorioBase = base_path('public/notes');

        $directorioNotas = '';
        if ($rutaPersonal) {
            $directorioNotas = $directorioBase.'/'.$rutaPersonal;
        }
        //  else {
        //     // $directorioNotas = $directorioBase;
        //     // $this->info("💡 Declare su Configuración de directorio personal ");
        //     $this->error("❌ El directorio directorio personal no ha sido configurado ");

        // }

        // Verificar que el directorio existe
        if (! is_dir($directorioNotas)) {
            $this->error('❌ El directorio personal no ha sido configurado '.$directorioNotas);
            // $this->info("💡 Ejecuta: mkdir -p " . $directorioNotas);
            $this->info('💡 Vaya a la sección de   Configuración  ⚙️');

            return 1;
            //  ⚙️ Configuración
        }

        $this->info('✅ Directorio encontrado: '.$directorioNotas);

        // 1.1. Convertir ~ a la ruta del home del usuario
        if (str_starts_with($directorioNotas, '~/')) {
            $home = getenv('HOME') ?: $_SERVER['HOME'] ?? '';
            $directorioNotas = $home.substr($directorioNotas, 1);
        }

        // // 2. Verificar que el directorio existe
        // if (!is_dir($directorioNotas)) {
        //     $this->error("El directorio no existe: " . $directorioNotas);
        //     return 1;
        // }

        //  // 2.1. Confirmar que encontramos el directorio
        // $this->info("✓ Directorio encontrado: " . $directorioNotas);

        // 3. Buscar todos los archivos .md (recursivamente)
        $this->info('Buscando archivos .md...');
        // 3. Escanear todos los archivos .md (incluyendo subdirectorios)

        $archivos = $this->obtenerArchivosMd($directorioNotas);

        $totalArchivos = count($archivos);
        $this->info('📄 Encontrados '.$totalArchivos.' archivos .md');

        if ($totalArchivos === 0) {
            $this->warn('⚠️ No hay archivos .md para importar');

            return 0;
        }

        // 4. Procesar cada archivo
        $contador = 0;
        // ##  Paso 1: Instalar la Libreria
        //
        //       composer require erusev/parsedown
        //
        $parsedown = new Parsedown;
        // $parsedown->setBreaksEnabled(true);

        // Reemplazar el Contenido del foreach, para proseguir con el procesador
        // de contenido markdown a HTML
        foreach ($archivos as $archivo) {
            $contador++;
            $this->info("[$contador/$totalArchivos] Procesando: ".basename($archivo));

            // 1. Obtener o crear la categoría
            $nombreCategoria = $this->obtenerCategoria($archivo, $directorioNotas);
            $categoriaId = null;

            if ($nombreCategoria) {
                // Explicación: Primero busca por slug (que es único).
                //  Si existe, usa esa categoría. Si no, la crea con el nombre actual.

                $slug = Str::slug($nombreCategoria);

                $categoria = Category::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $nombreCategoria]
                );

                $categoriaId = $categoria->id;
                $this->line('   📂 Categoría: '.$nombreCategoria.' (ID: '.$categoriaId.')');
            } else {
                // Sin categoría -> asignar "General"
                $categoria = Category::firstOrCreate(
                    ['slug' => 'general'],
                    ['name' => 'General']
                );
                $categoriaId = $categoria->id;
                $this->line('   📂 Categoría: '.$nombreCategoria.' (ID: '.$categoriaId.')');
            }

            // 2. Leer el contenido del archivo
            $contenido = File::get($archivo);

            // 3. Convertir Markdown a HTML
            $html = $parsedown->text($contenido);

            // Normalizar rutas de imágenes (AGREGAR ESTAS LÍNEAS)
            $html = $this->normalizarRutasImagenes($html);

            // 4. Extraer título
            $titulo = $this->extraerTitulo($contenido, basename($archivo));
            // Truncar a 250 caracteres máximo
            if (strlen($titulo) > 250) {
                $titulo = substr($titulo, 0, 247).'...';
            }

            $this->line('   📝 Título: '.$titulo);

            // 5. Calcular checksum (hash del contenido)
            $checksum = md5($contenido);

            // 6. Verificar si la nota ya existe (por file_path o checksum)
            $notaExistente = Note::where('file_path', $archivo)->first();

            if ($notaExistente && $notaExistente->checksum === $checksum) {
                $this->line('   ⏭️ Sin cambios, omitida');

                continue;
            }

            // 7. Crear o actualizar la nota
            $nota = Note::updateOrCreate(
                ['file_path' => $archivo],
                [
                    'title' => $titulo,
                    'slug' => Str::slug($titulo).'-'.uniqid(), // Asegura slugs únicos
                    'content_markdown' => $contenido,
                    'content_html' => $html,
                    'checksum' => $checksum,
                    'category_id' => $categoriaId,
                    'user_id' => $usuario->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->line('   ✅ Nota guardada (ID: '.$nota->id.')');
            $this->line('');
        }

        return 0;
    }

    /**
     * Obtiene todos los archivos .md de un directorio y sus subdirectorios
     */
    private function obtenerArchivosMd(string $directorio): array
    {
        $archivos = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directorio, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $archivo) {
            if ($archivo->getExtension() === 'md') {
                $archivos[] = $archivo->getPathname();
            }
        }

        return $archivos;
    }

    /**
     * Obtiene la categoría basada en el nombre del subdirectorio
     */
    private function obtenerCategoria(string $rutaArchivo, string $directorioBase): ?string
    {
        $directorioArchivo = dirname($rutaArchivo);

        // Si el archivo está en el directorio base, no tiene categoría
        if ($directorioArchivo === $directorioBase) {
            return null;
        }

        // Extraer el nombre del subdirectorio inmediato
        $relativo = str_replace($directorioBase.DIRECTORY_SEPARATOR, '', $directorioArchivo);
        $partes = explode(DIRECTORY_SEPARATOR, $relativo);

        return $partes[0]; // Primer subdirectorio
    }

    /**
     * Extrae el título del contenido Markdown
     */
    private function extraerTitulo(string $contenido, string $nombreArchivo): string
    {
        // Buscar primera línea que empiece con #
        $lineas = explode("\n", $contenido);
        foreach ($lineas as $linea) {
            if (str_starts_with(trim($linea), '# ')) {
                // Eliminar el # y espacios, retornar el título
                return trim(substr(trim($linea), 2));
            }
        }

        // Si no hay título, usar el nombre del archivo (sin extensión)
        return pathinfo($nombreArchivo, PATHINFO_FILENAME);
    }

    /**
     * Normaliza rutas de imágenes en el HTML generado
     */
    private function normalizarRutasImagenes(string $html): string
    {
        // Patrón 1: Ruta absoluta de Linux en formato Markdown
        // Busca: ![texto](/home/datenmaniak/notes/images/archivo.png)
        // Reemplaza: ![texto](/images/archivo.png)
        $html = preg_replace(
            '/src="\/home\/datenmaniak\/notes\/images\//',
            'src="/images/',
            $html
        );

        // Retornar el HTML con las rutas normalizadas
        return $html;
    }

    // 5. Mostrar mensajes de progreso en la terminal
    // 6. Al final, mostrar un resumen (cuántas notas se importaron)

}
```



## app/Console/Commands/ShowNotesCommand.php

```php
<?php

namespace App\Console\Commands;

use App\Models\Note;
// use Illuminate\Console\Attributes\Description;
// use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand; // 🌟 Este es el namespace real de Symfony que usa Laravel 12 de fondo

#[AsCommand(name: 'notes:show', description: 'Mostrar las notas importadas')]
class ShowNotesCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notes = Note::with('category')->get();

        if ($notes->isEmpty()) {
            $this->warn('No hay notas en la base de datos.');

            return 0;
        }

        $this->info('=== NOTAS IMPORTADAS ===');

        foreach ($notes as $note) {
            $this->line('');
            $this->line("ID: {$note->id}");
            $this->line("Título: {$note->title}");
            $this->line('Categoría: '.($note->category?->name ?? 'sin categoría'));
            $this->line("Ruta: {$note->file_path}");
            $this->line('Checksum: '.substr($note->checksum, 0, 16).'...');
            $this->line("Creado: {$note->created_at}");
            $this->line('---');
        }

        $this->line('');
        $this->info("Total: {$notes->count()} notas");

        return 0;
    }
}
```

