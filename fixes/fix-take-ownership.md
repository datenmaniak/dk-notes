# tomar posesion de las notas


Evaluar estas acciones en la sincronizacion:

1. Asignar al usuario todas las notas considerando su propio directorio personal.
2. Asignar permisos de usuario/grupo al directorio personal, como: www-data
3. Mantener el resto de las acciones de la funcion de sincronizacion


## funcion de sincronizacion

```php
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
```

