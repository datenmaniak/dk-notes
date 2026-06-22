<x-app-layout>
    <div class="p-6 pt-16 lg:pt-6 max-w-4xl mx-auto">
        {{-- Contenedor principal --}}
        <div class="card-daten shadow-sm p-6 transition-all duration-300">
            
            {{-- Enlace de retorno --}}
            <div class="mb-6">
                @php
                    $currentFilter = session('last_notes_filter');
                    $queryParams = session('last_notes_filter') ? ['category' => session('last_notes_filter'), 'page' => $page ?? 1] : ['page' => $page ?? 1];
                @endphp
                <a href="{{ route('notes.show', ['note' => $note] + $queryParams) }}"
                   class="text-sm font-medium text-daten-secondary hover:text-brand-glow hover:bg-brand-glow/10 px-3 py-2 inline-flex items-center gap-2 transition-all duration-200 rounded-lg group">
                    <i class="fa-solid fa-arrow-left text-xs text-daten-muted group-hover:text-brand-glow transition-colors"></i> 
                    Volver a la nota
                </a>
            </div>
            
            {{-- Cabecera de la sección --}}
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-daten">
                <div class="w-12 h-12 rounded-lg bg-brand-glow/10 flex items-center justify-center text-brand-glow">
                    <i class="fa-solid fa-pen-to-square text-xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-daten-primary tracking-tight">Editar Nota</h1>
            </div>
            
            <form method="POST" action="{{ route('notes.update', $note) }}">
                @csrf
                @method('PUT')
                
                {{-- Control de ubicación del paginador --}}
                <input type="hidden" name="page" value="{{ $page ?? 1 }}">
                
                {{-- Bloque superior de acciones fijas --}}
                <div class="flex flex-wrap gap-3 justify-end mb-6">
                    <a href="{{ route('notes.show', ['note' => $note] + $queryParams) }}" 
                       class="px-4 py-2 border border-daten text-daten-secondary bg-daten-card hover:bg-brand-glow/10 hover:text-brand-glow rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 active:scale-[0.98]">
                        <i class="fa-solid fa-ban text-daten-muted group-hover:text-brand-glow"></i> Cancelar
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-brand-glow hover:bg-brand-accent text-white rounded-lg text-sm font-medium shadow-md shadow-brand-glow/20 hover:shadow-lg hover:shadow-brand-glow/30 transition-all duration-200 flex items-center gap-2 active:scale-[0.98]">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                    </button>
                </div>

                {{-- Campo: Título --}}
                <div class="mb-5 space-y-2">
                    <label class="text-xs font-semibold text-daten-secondary uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-heading text-daten-muted"></i> Título de la nota
                    </label>
                    <input type="text" name="title" value="{{ old('title', $note->title) }}" 
                           class="w-full input-daten text-sm placeholder:text-daten-muted focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30 transition-all duration-200 rounded-lg" required>
                </div>
                
                {{-- Campo: Categoría --}}
                <div class="mb-5 space-y-2">
                    <label class="text-xs font-semibold text-daten-secondary uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-folder text-daten-muted"></i> Categoría
                    </label>
                    <select name="category_id" 
                            class="w-full input-daten text-sm focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30 transition-all duration-200 rounded-lg appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2224%22 height=%2224%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2364748b%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22%3E%3Cpolyline points=%226 9 12 15 18 9%22/%3E%3C/svg%3E')] bg-[length:1.5rem] bg-[right:1rem_center] bg-no-repeat pr-12">
                        <option value="">Sin categoría</option>
                        @foreach(\App\Models\Category::all() as $category)
                            <option value="{{ $category->id }}" {{ $note->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-daten-muted flex items-center gap-1">
                        <i class="fa-solid fa-info-circle"></i> Selecciona una categoría existente para organizar tu nota.
                    </p>
                </div>

                {{-- Contenedor de Etiquetas Existentes e Invocador de Modal --}}
                <div class="mb-5 bg-daten-input/50 rounded-xl border border-dashed border-daten p-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-semibold text-daten-secondary uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-tags text-daten-muted"></i> Etiquetas asignadas
                            </span>
                            <div id="noteTagsContainer" class="flex flex-wrap gap-1.5 mt-1">
                                <!-- Las etiquetas se inyectan dinámicamente vía JS -->
                            </div>
                        </div>
                        <button type="button" onclick="openAssignTagsModal()"
                                class="px-3 py-1.5 text-xs text-daten-secondary border border-daten bg-daten-card rounded-lg hover:bg-brand-glow/10 hover:text-brand-glow focus:ring-2 focus:ring-brand-glow/30 transition-all duration-200 font-medium flex items-center gap-1.5">
                            <i class="fa-solid fa-pen-to-square text-sm"></i> Administrar etiquetas
                        </button>
                    </div>
                </div>

                {{-- Campo: Contenido Markdown --}}
                <div class="mb-6 space-y-2">
                    <label class="text-xs font-semibold text-daten-secondary uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-code text-daten-muted"></i> Contenido (Markdown)
                    </label>
                    <textarea name="content_markdown" rows="15" 
                              class="w-full input-daten font-mono text-sm placeholder:text-daten-muted focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30 transition-all duration-200 rounded-lg" required>{{ old('content_markdown', $note->content_markdown) }}</textarea>
                    <p class="text-[11px] text-daten-muted flex items-center gap-1">
                        <i class="fa-solid fa-info-circle"></i> Utiliza sintaxis Markdown para dar formato a tu nota.
                    </p>
                </div>

                {{-- Modal de asignación de etiquetas --}}
                <div id="assignTagsModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center transition-all p-4">
                    <div class="bg-daten-card rounded-xl border border-daten shadow-2xl max-w-md w-full mx-4 overflow-hidden max-h-[90vh] flex flex-col">
                        {{-- Título interno del modal --}}
                        <div class="p-6 border-b border-daten flex items-center gap-2.5 text-daten-primary flex-shrink-0">
                            <i class="fa-solid fa-tags text-brand-glow text-xl"></i>
                            <h3 class="text-xl font-bold tracking-tight">Asignar etiquetas</h3>
                        </div>
                        <div class="p-6 overflow-y-auto flex-1">
                            <div id="allTagsList" class="space-y-1.5">
                                <!-- Checkboxes se cargarán vía JS -->
                            </div>
                        </div>
                        <div class="p-6 pt-4 border-t border-daten flex justify-end gap-3 flex-shrink-0">
                            <button type="button" onclick="closeAssignTagsModal()" 
                                    class="px-4 py-2 border border-daten text-daten-secondary bg-daten-card hover:bg-brand-glow/10 hover:text-brand-glow rounded-lg text-sm font-medium transition-all duration-200">
                                Cancelar
                            </button>
                            <button type="button" onclick="saveAssignedTags()" 
                                    class="px-4 py-2 bg-brand-glow hover:bg-brand-accent text-white rounded-lg text-sm font-medium shadow-md shadow-brand-glow/20 hover:shadow-lg hover:shadow-brand-glow/30 transition-all duration-200">
                                <i class="fa-solid fa-check mr-1.5"></i> Confirmar Asignación
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Lógica de JavaScript --}}
                <script>
                    let currentNoteId = {{ $note->id }};
                    let assignedTags = [];
                    let allTags = [];
                    
                    function loadNoteTags() {
                        fetch('{{ route("notes.tags.get", $note) }}')
                            .then(response => response.json())
                            .then(tags => {
                                assignedTags = tags;
                                updateTagsDisplay();
                            });
                    }
                    
                    function updateTagsDisplay() {
                        const container = document.getElementById('noteTagsContainer');
                        if (assignedTags.length === 0) {
                            container.innerHTML = `<span class="text-xs text-daten-muted italic flex items-center gap-1"><i class="fa-solid fa-circle-info"></i> Sin etiquetas asignadas</span>`;
                            return;
                        }
                        container.innerHTML = assignedTags.map(tag => 
                            `<span class="inline-flex items-center gap-1.5 bg-brand-glow/10 text-brand-glow px-2.5 py-1 rounded-md text-xs font-medium border border-brand-glow/20">
                                <i class="fa-solid fa-tag text-brand-glow/60 text-[10px]"></i> ${tag.name}
                            </span>`
                        ).join('');
                    }
            
                    function openAssignTagsModal() {
                        fetch('{{ route("tags.index") }}')
                            .then(response => response.json())
                            .then(tags => {
                                allTags = tags;
                                const container = document.getElementById('allTagsList');
                                if (allTags.length === 0) {
                                    container.innerHTML = `
                                        <div class="text-center py-8 text-daten-muted">
                                            <i class="fa-solid fa-tags text-3xl block mb-3 opacity-30"></i>
                                            <p class="text-sm">No hay etiquetas disponibles</p>
                                            <p class="text-xs mt-1">Crea etiquetas desde el panel principal</p>
                                        </div>
                                    `;
                                    return;
                                }
                                container.innerHTML = allTags.map(tag => `
                                    <label class="flex items-center gap-3 p-2.5 hover:bg-brand-glow/5 rounded-lg cursor-pointer border border-transparent hover:border-brand-glow/15 transition-all group">
                                        <input type="checkbox" value="${tag.id}" 
                                            ${assignedTags.some(t => t.id === tag.id) ? 'checked' : ''}
                                            class="w-4 h-4 rounded border-daten text-brand-glow focus:ring-2 focus:ring-brand-glow/30 transition-all">
                                        <div class="flex items-center gap-2 text-sm font-medium text-daten-secondary group-hover:text-daten-primary">
                                            <i class="fa-solid fa-tag text-daten-muted group-hover:text-brand-glow/60 transition-colors text-xs"></i>
                                            <span>${tag.name}</span>
                                        </div>
                                    </label>
                                `).join('');
                                document.getElementById('assignTagsModal').classList.remove('hidden');
                            });
                    }
                    
                    function closeAssignTagsModal() {
                        document.getElementById('assignTagsModal').classList.add('hidden');
                    }
                
                    function saveAssignedTags() {
                        const selected = Array.from(document.querySelectorAll('#allTagsList input:checked'))
                            .map(cb => parseInt(cb.value));
                        
                        fetch('{{ route("notes.tags.assign", $note) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ tags: selected })
                        }).then(() => {
                            loadNoteTags();
                            closeAssignTagsModal();
                        });
                    }
                    
                    loadNoteTags();
                </script>
            </form>
        </div>
    </div>
</x-app-layout>