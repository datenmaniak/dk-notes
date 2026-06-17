# Identificar error de ShowNotesCommand.php

Error durante la Fase II de CI


**Mensaje**

```txt
                                                                              
  The command defined in "App\Console\Commands\ShowNotesCommand" cannot have   
  an empty name.                                                               
                                                                               

Error: Process completed with exit code 1.
```

## Componente involucrado

```bash
~/dk-notes/app/Console/Commands/ShowNotesCommand.php
```


## Codigo actual

```php
<?php

namespace App\Console\Commands;

use App\Models\Note;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('notes:show')]
#[Description('Mostrar las notas importadas')]
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

