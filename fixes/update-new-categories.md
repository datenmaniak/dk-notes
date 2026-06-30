# Actualizar en la base de datos la categoria personalizada

**Especificacion de lo deseado**
Al subir una nota el usuario puede asignar una categoria nueva. Esta nueva categoria debe registrar en la base de datos asociado al usuario que la ha creado.

**Fallo encontrado:**
Se registra la nueva categoria en la base de datos. Sin embargo, no se asocia/registra el `user_id` del usuario que ha creado la nueva categoria.


## Llamado a la funcion para subir una nota nueva
```php
                <form id="uploadForm" method="POST" action="{{ route('notes.upload') }}" enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf
                    
                    {{-- Selección de archivo --}}
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-daten-secondary  tracking-wider">Archivo Markdown (.md) *</label>
                        <input type="file" name="file" accept=".md" required
                            class="w-full text-sm text-daten-secondary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border file:border-daten file:text-xs file:font-semibold file:bg-daten-input file:text-daten-secondary hover:file:bg-brand-glow/10 hover:file:text-brand-glow hover:file:border-brand-glow/30 file:transition-all cursor-pointer">
                        <p class="text-[11px] text-daten-muted">El sistema procesará la metadata y el Front Matter del archivo.</p>
                    </div>
                    
                    {{-- Categorías existentes --}}
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-daten-secondary uppercase tracking-wider">Categoría existente (opcional)</label>
                        <select name="category_id" class="w-full px-4 py-3 rounded-lg border border-daten bg-daten-input text-sm text-daten-primary focus:outline-none focus:border-brand-glow focus:ring-4 focus:ring-brand-glow/15 transition-all duration-200">
                            <option value="">- Ninguna "General", nueva personalizada o elija de la lista -</option>
                            @foreach(auth()->user()->categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Nueva categoría --}}
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-daten-secondary uppercase tracking-wider">Nueva categoría personalizada (opcional)</label>
                        <input type="text" name="new_category" class="w-full px-4 py-3 rounded-lg border border-daten bg-daten-input text-sm text-daten-primary focus:outline-none focus:border-brand-glow focus:ring-4 focus:ring-brand-glow/15 transition-all duration-200" 
                            placeholder="Ej: Laravel, Docker, GitOps">
                        <p class="text-[11px] text-daten-muted">Si el campo tiene texto, se priorizará la creación de esta categoría automáticamente.</p>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" onclick="closeUploadModal()" 
                                class="px-4 py-2  text-daten-secondary bg-daten-card hover:bg-brand-glow/10 hover:text-brand-glow rounded-lg text-sm font-medium transition-all duration-200">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-md shadow-emerald-600/10 transition-all duration-200">
                            Procesar y Subir
                        </button>
                    </div>
                </form>
```




## NoteController.php

**function:** upload

Codigo fuente PHP
```php
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
    ```





