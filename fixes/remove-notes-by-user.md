# Eliminar notas 

Ajustar para que la funcion elimine solo lo que es propiedad del usuario



## Especificaciones

Aplica a la base de datos de:

- Notas
- Categorias
- Etiquetas

## funciones actuales de la aplicacion asociado a eliminacion de registros


### Setting
**/var/home/datenmaniak/dk-notes/app/resources/views/settings/index.blade.php**


```php
{{-- Zona peligrosa - Solo visible para administrador --}}
@if(Auth::user()->is_admin)
    <div class="mt-8 p-5 border-2 border-red-500/40 dark:border-red-500/30 bg-red-50 dark:bg-red-950/20 rounded-xl">
        <h3 class="text-lg font-semibold text-red-700 dark:text-red-400 mb-2 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Zona peligrosa
        </h3>
        <p class="text-sm text-red-600 dark:text-red-300 mb-4">
            Esta acción eliminará <strong>TODAS las notas</strong> permanentemente.<br>
            Las categorías, usuarios y configuraciones no se verán afectadas.
        </p>
        <form method="POST" action="{{ route('notes.delete-all') }}" 
            onsubmit="return confirm('¿Eliminar TODAS las notas? Esta acción no se puede deshacer.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-all duration-200 flex items-center gap-2 active:scale-[0.98]">
                <i class="fa-solid fa-trash-can"></i>
                Eliminar todas las notas
            </button>
        </form>
    </div>
@endif
```
--- 
### NoteController
**/var/home/datenmaniak/dk-notes/app/app/Http/Controllers/NoteController.php**

```php
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
```

## 



