# identificar la causa de Internal Server Error

##  Error

```plaintext
Symfony\Component\Console\Exception\InvalidOptionException

vendor/symfony/console/Input/ArrayInput.php:154

The "--user" option does not exist.
```


## Trace

```console
Illuminate\Support\Facades\Facade::__callStatic(string, array)
app/Http/Controllers/NoteController.php:171

166        // Log para verificar que se ejecuta el botón
167        Log::info('=== INICIO Sincronización desde web ===');
168        Log::info('Directorio: '.$directorioConfigurado);
169
170        // Ejecutar importación pasando el ID del usuario actual
171        Artisan::call('notes:import', ['--user' => Auth::id()]);
172        $output = Artisan::output();

```

## Comprobaciones

```console
/var/www/html # php artisan notes:import --user=1

                                       
  The "--user" option does not exist.  
                                       

/var/www/html # php artisan notes:import 1

                                                              
  No arguments expected for "notes:import" command, got "1".  
                                                              

```
