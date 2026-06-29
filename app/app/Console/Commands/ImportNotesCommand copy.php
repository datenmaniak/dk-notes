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
     * La firma de consola que define las opciones aceptadas.
     * 🌟 Al agregar {--user=}, Laravel habilita oficialmente la bandera '--user'
     */
    protected $signature = 'notes:import {--user=}';

    /**
     * Execute the console command.
     */
    public function handle()
    {

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
                    ['slug' => $slug,
                        'name' => $nombreCategoria,
                        'user_id' => $usuario->id],
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
            // $html = $this->normalizarRutasImagenes($html);

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

        // ... (Todo el código inicial del comando se mantiene igual)

        foreach ($archivos as $archivo) {
            $contador++;
            $this->info("[$contador/$totalArchivos] Procesando: ".basename($archivo));

            // 1. Obtener o crear la categoría
            $nombreCategoria = $this->obtenerCategoria($archivo, $directorioNotas);
            $categoriaId = null;

            if ($nombreCategoria) {
                $slug = Str::slug($nombreCategoria);

                // 🔍 AJUSTE CRÍTICO: Buscar o crear la categoría amarrada al usuario actual
                $categoria = Category::firstOrCreate(
                    [
                        'slug' => $slug,
                        'user_id' => $usuario->id, // Aislamiento multiusuario
                    ],
                    [
                        'name' => $nombreCategoria,
                    ]
                );

                $categoriaId = $categoria->id;
                $this->line('   📂 Categoría: '.$nombreCategoria.' (ID: '.$categoriaId.')');
            } else {
                // Sin categoría -> asignar "General" amarrada al usuario actual
                $categoria = Category::firstOrCreate(
                    [
                        'slug' => 'general',
                        'user_id' => $usuario->id, // Aislamiento multiusuario
                    ],
                    [
                        'name' => 'General',
                    ]
                );
                $categoriaId = $categoria->id;
                $this->line('   📂 Categoría: General (ID: '.$categoriaId.')');
            }

            // Dentro del foreach de archivos:
            $this->asegurarPermisosManejador(dirname($archivo)); // Asegura la carpeta de la categoría
            $this->asegurarPermisosManejador($archivo);          // Asegura el archivo .md

            // 2. Leer el contenido del archivo
            $contenido = File::get($archivo);

            // ... (El resto del ciclo foreach de notas continúa igual)
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
    // private function normalizarRutasImagenes(string $html): string
    // {
    //     // Patrón 1: Ruta absoluta de Linux en formato Markdown
    //     // Busca: ![texto](/home/datenmaniak/notes/images/archivo.png)
    //     // Reemplaza: ![texto](/images/archivo.png)
    //     $html = preg_replace(
    //         '/src="\/home\/datenmaniak\/notes\/images\//',
    //         'src="/images/',
    //         $html
    //     );

    //     // Retornar el HTML con las rutas normalizadas
    //     return $html;
    // }

    // 5. Mostrar mensajes de progreso en la terminal
    // 6. Al final, mostrar un resumen (cuántas notas se importaron)

    /**
     * Asegura los permisos correctos (chmod) del directorio y archivos de notas.
     * Intenta aplicar chown/chgrp solo si el entorno del contenedor lo permite.
     */
    private function asegurarPermisosManejador(string $ruta): void
    {
        try {
            if (is_dir($ruta)) {
                // Asegurar que el directorio sea accesible (Lectura/Escritura/Ejecución)
                @chmod($ruta, 0755);

                // Opcional: Intentar asignar UID/GID 33 si PHP corre con suficientes privilegios
                @chown($ruta, 33);
                @chgrp($ruta, 33);
            } elseif (is_file($ruta)) {
                @chmod($ruta, 0644);
                @chown($ruta, 33);
                @chgrp($ruta, 33);
            }
        } catch (\Exception $e) {
            // Silenciar errores si el sistema operativo restringe la operación
        }
    }
}
