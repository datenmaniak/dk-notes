<?php

/**
 * Herramienta administrativa: Sincronizar hora y zona horaria
 * 
 * Ejecutar: php artisan tinker
 * Luego: include('scripts/sync-time.php');
 * 
 * Opciones:
 * - Configurar zona horaria de Laravel
 * - Sincronizar hora del sistema con NTP (requiere ntpdate)
 */

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

// Verificar que se ejecuta desde CLI
if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ejecutarse desde la línea de comandos.\n");
}

// Zonas horarias disponibles
$timezones = [
    'America/Caracas' => 'Venezuela',
    'America/Argentina/Buenos_Aires' => 'Argentina',
    'America/Mexico_City' => 'México (Centro)',
    'America/Monterrey' => 'México (Norte)',
    'America/Cancun' => 'México (Sureste)',
    'Europe/Madrid' => 'España',
    'America/Bogota' => 'Colombia',
    'America/Santiago' => 'Chile',
    'America/Lima' => 'Perú',
    'America/Guayaquil' => 'Ecuador',
    'America/La_Paz' => 'Bolivia',
    'America/Asuncion' => 'Paraguay',
    'America/Montevideo' => 'Uruguay',
    'America/Panama' => 'Panamá',
    'America/Costa_Rica' => 'Costa Rica',
    'America/El_Salvador' => 'El Salvador',
    'America/Guatemala' => 'Guatemala',
    'America/Tegucigalpa' => 'Honduras',
    'America/Managua' => 'Nicaragua',
    'America/Santo_Domingo' => 'República Dominicana',
    'America/Puerto_Rico' => 'Puerto Rico',
    'America/Havana' => 'Cuba',
    'UTC' => 'UTC (Tiempo Universal)',
];

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "     SINCRONIZAR HORA - DKNotes\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Mostrar hora actual del sistema
echo "📅 Hora actual del sistema:\n";
echo "   PHP:    " . date('Y-m-d H:i:s') . "\n";
echo "   MySQL:  " . DB::select('SELECT NOW() as now')[0]->now . "\n";

// Verificar si ntpdate está disponible
$ntpAvailable = shell_exec('which ntpdate 2>/dev/null');
// $hasNtp = !empty(trim($ntpAvailable));
$hasNtp = !empty(trim($ntpAvailable ?? ''));

echo "\n───────────────────────────────────────────────────────────\n";
echo "Opciones:\n";
echo "  1. Configurar zona horaria de Laravel\n";
if ($hasNtp) {
    echo "  2. Sincronizar hora con NTP (ntpdate)\n";
} else {
    echo "  2. Sincronizar hora con NTP (requiere instalar ntpdate)\n";
}
echo "  3. Ambas (recomendado)\n";
echo "  4. Salir\n";
echo "───────────────────────────────────────────────────────────\n";

$opcion = readline("\nSeleccione una opción (1-4): ");

if ($opcion == 4) {
    echo "\n❌ Cancelado.\n\n";
    exit;
}

if (!in_array($opcion, ['1', '2', '3'])) {
    echo "\n❌ Opción inválida.\n\n";
    exit;
}

// Seleccionar zona horaria
$selectedTz = '';
if (in_array($opcion, ['1', '3'])) {
    echo "\n───────────────────────────────────────────────────────────\n";
    echo "Seleccione zona horaria:\n";
    
    $i = 1;
    foreach ($timezones as $tz => $description) {
        echo "  {$i}. {$description} ({$tz})\n";
        $i++;
    }
    
    $tzOption = readline("\nNúmero de zona horaria (1-" . count($timezones) . "): ");
    $tzKeys = array_keys($timezones);
    $selectedTz = $tzKeys[$tzOption - 1] ?? null;
    
    if (!$selectedTz) {
        echo "\n❌ Zona horaria inválida.\n\n";
        exit;
    }
}

// Aplicar cambios
try {
    // 1. Configurar zona horaria de Laravel
    if (in_array($opcion, ['1', '3']) && $selectedTz) {
        echo "\n🔄 Configurando zona horaria: {$selectedTz}\n";
        
        // Actualizar archivo config/app.php
        $configPath = base_path('config/app.php');
        $configContent = file_get_contents($configPath);
        $configContent = preg_replace(
            "/'timezone' => '[^']*'/",
            "'timezone' => '{$selectedTz}'",
            $configContent
        );
        file_put_contents($configPath, $configContent);
        
        // Limpiar caché
        Artisan::call('config:clear');
        
        echo "  ✅ Zona horaria configurada a: {$selectedTz}\n";
    }
    
    // 2. Sincronizar hora con NTP
    if (in_array($opcion, ['2', '3'])) {
        if ($hasNtp) {
            echo "\n🔄 Sincronizando hora con NTP...\n";
            $output = shell_exec('sudo ntpdate -u pool.ntp.org 2>&1');
            echo "  Resultado: " . ($output ? trim($output) : "Sincronizado") . "\n";
            echo "  ✅ Hora sincronizada.\n";
        } else {
            echo "\n⚠️  ntpdate no está instalado.\n";
            echo "  Para instalarlo, ejecuta:\n";
            echo "  apt-get update && apt-get install -y ntpdate\n";
            echo "  Luego ejecuta nuevamente el script.\n";
        }
    }
    
    // Mostrar nueva hora
    echo "\n───────────────────────────────────────────────────────────\n";
    echo "📅 Nueva hora del sistema:\n";
    echo "   PHP:    " . date('Y-m-d H:i:s') . "\n";
    echo "   MySQL:  " . DB::select('SELECT NOW() as now')[0]->now . "\n";
    echo "───────────────────────────────────────────────────────────\n";
    
    echo "\n═══════════════════════════════════════════════════════════\n";
    echo "✅ Sincronización completada.\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "   Línea: " . $e->getLine() . "\n\n";
    exit(1);
}