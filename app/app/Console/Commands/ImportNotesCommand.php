<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Note;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
// as FacadesFile;


use Parsedown;

#[Signature('notes:import')]
#[Description('Importa notas Markdown desde un directorio')]
class ImportNotesCommand extends Command
{
    /**
     * Execute the console command.
     */
    // ← El nombre que escribirás en terminal


    public function handle()
    {

    // 1. Definir la ruta del directorio de notas
    // $directorioNotas = 'notes'; // REMOVE
    $directorioNotas = base_path('notes');

    // 1.1. Convertir ~ a la ruta del home del usuario
    if (str_starts_with($directorioNotas, '~/')) {
        $home = getenv('HOME') ?: $_SERVER['HOME'] ?? '';
        $directorioNotas = $home . substr($directorioNotas, 1);
    }

    // 2. Verificar que el directorio existe
    if (!is_dir($directorioNotas)) {
        $this->error("El directorio no existe: " . $directorioNotas);
        return 1;
    }

     // 2.1. Confirmar que encontramos el directorio
    $this->info("✓ Directorio encontrado: " . $directorioNotas);

    // 3. Buscar todos los archivos .md (recursivamente)
    $this->info("Buscando archivos .md...");
    // 3. Escanear todos los archivos .md (incluyendo subdirectorios)

    $archivos = $this->obtenerArchivosMd($directorioNotas);

    $totalArchivos = count($archivos);
    $this->info("📄 Encontrados " . $totalArchivos . " archivos .md");

    if ($totalArchivos === 0) {
            $this->warn("⚠️ No hay archivos .md para importar");
            return 0;
    }

     // 4. Procesar cada archivo
    $contador = 0;
    // ##  Paso 1: Instalar la Libreria
    //
    //       composer require erusev/parsedown
    //
    $parsedown = new Parsedown();

    // Obtener el usuario administrador (el primero creado)
    $usuario = User::first();
    if (!$usuario) {
        $this->error("❌ No hay usuarios en el sistema. Crea un usuario primero.");
        return 1;
    }

    // Reemplazar el Contenido del foreach, para proseguir con el procesador
    // de contenido markdown a HTML
    foreach ($archivos as $archivo) {
        $contador++;
        $this->info("[$contador/$totalArchivos] Procesando: " . basename($archivo));
        
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
            $this->line("   📂 Categoría: " . $nombreCategoria . " (ID: " . $categoriaId . ")");
        } else {
            $this->line("   📂 Sin categoría");
        }
        
        // 2. Leer el contenido del archivo
        $contenido = File::get($archivo);
        
        // 3. Convertir Markdown a HTML
        $html = $parsedown->text($contenido);

        // Normalizar rutas de imágenes (AGREGAR ESTAS LÍNEAS)
        $html = $this->normalizarRutasImagenes($html);
        
        // 4. Extraer título
        $titulo = $this->extraerTitulo($contenido, basename($archivo));
        $this->line("   📝 Título: " . $titulo);
        
        // 5. Calcular checksum (hash del contenido)
        $checksum = md5($contenido);
        
        // 6. Verificar si la nota ya existe (por file_path o checksum)
        $notaExistente = Note::where('file_path', $archivo)->first();
        
        if ($notaExistente && $notaExistente->checksum === $checksum) {
            $this->line("   ⏭️ Sin cambios, omitida");
            continue;
        }
        
        // 7. Crear o actualizar la nota
        $nota = Note::updateOrCreate(
            ['file_path' => $archivo],
            [
                'title' => $titulo,
                'slug' => Str::slug($titulo) . '-' . uniqid(), // Asegura slugs únicos
                'content_markdown' => $contenido,
                'content_html' => $html,
                'checksum' => $checksum,
                'category_id' => $categoriaId,
                'user_id' => $usuario->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        
        $this->line("   ✅ Nota guardada (ID: " . $nota->id . ")");
        $this->line("");
    }

    return 0;
}

    /**
     * Obtiene todos los archivos .md de un directorio y sus subdirectorios
     */
    private function obtenerArchivosMd(string $directorio): array
    {
        $archivos = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directorio, \RecursiveDirectoryIterator::SKIP_DOTS)
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
        $relativo = str_replace($directorioBase . DIRECTORY_SEPARATOR, '', $directorioArchivo);
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
