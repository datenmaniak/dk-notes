<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File as FacadesFile;
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
    $directorioNotas = 'notes';

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
    foreach ($archivos as $archivo) {
        $contador++;
        $this->line("[$contador/$totalArchivos] Procesando: " . basename($archivo));

        // Obtener la categoría basada en el subdirectorio
        $categoria = $this->obtenerCategoria($archivo, $directorioNotas);
        $this->line("   📂 Categoría: " . ($categoria ?: 'sin categoría'));

        // Leer el contenido del archivo
        $contenido = FacadesFile::get($archivo);
        // $contenido = File::get($archivo);

        // Convertir Markdown a HTML
        $html = $parsedown->text($contenido);

        // Extraer título (primera línea que empiece con #)
        $titulo = $this->extraerTitulo($contenido, basename($archivo));
        $this->line("   📝 Título: " . $titulo);

        // Aquí después guardaremos en la base de datos
        $this->line("");
    }
    $this->info("✅ Importación completada");

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

    // 5. Mostrar mensajes de progreso en la terminal
    // 6. Al final, mostrar un resumen (cuántas notas se importaron)


    }
