<x-app-layout>
    <div class="p-6 pt-16 lg:pt-6 max-w-4xl mx-auto">
        {{-- Contenedor principal estilizado con el suave borde de marca --}}
        <div class="bg-white rounded-xl border border-[rgba(119,0,240,0.15)] shadow-sm p-6 transition-all duration-300">
            
            {{-- Enlace de retorno --}}
            <div class="mb-6">
                 @php
                    $currentFilter = session('last_notes_filter');
                    $queryParams = session('last_notes_filter') ? ['category' => session('last_notes_filter'), 'page' => $page ?? 1] : ['page' => $page ?? 1];
                @endphp
                <a href="{{ route('notes.show', ['note' => $note] + $queryParams) }}"
                   class="text-sm font-medium text-slate-600 hover:text-[#7700F0] hover:bg-purple-50 px-3 py-2 inline-flex items-center gap-2 transition-all rounded-lg group">
                    <i class="fa-solid fa-arrow-left text-xs text-slate-400 group-hover:text-[#7700F0] transition-colors"></i> 
                    Volver a la nota
                </a>
            </div>
            
            {{-- Cabecera de la sección --}}
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-lg bg-[#7700F0]/10 flex items-center justify-center text-[#7700F0]">
                    <i class="fa-solid fa-pen-to-square text-lg"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-900">Editar Nota</h1>
            </div>
            
            <form method="POST" action="{{ route('notes.update', $note) }}">
                @csrf
                @method('PUT')
                
                {{-- Control de ubicación del paginador --}}
                <input type="hidden" name="page" value="{{ $page ?? 1 }}">
                
                {{-- Bloque superior de acciones fijas --}}
                <div class="flex gap-3 justify-end mb-6">
                    <a href="{{ route('notes.show', ['note' => $note] + $queryParams) }}" 
                       class="px-4 py-2 border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 rounded-lg text-sm font-medium transition-all flex items-center gap-2 active:scale-95">
                        <i class="fa-solid fa-ban text-slate-400"></i> Cancelar
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-[#7700F0] hover:bg-[#7d2eff] text-white rounded-lg text-sm font-medium shadow-md shadow-[#7700F0]/20 transition-all flex items-center gap-2 active:scale-95">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                    </button>
                </div>

                {{-- Campo: Título --}}
                <div class="mb-5 space-y-2">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Título de la nota</label>
                    <input type="text" name="title" value="{{ old('title', $note->title) }}" 
                           class="w-full px-4 py-3 rounded-lg border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:border-[#7700F0] focus:ring-1 focus:ring-[#7700F0]/30 transition-all" required>
                </div>
                
                {{-- Campo: Categoría --}}
                <div class="mb-5 space-y-2">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Categoría</label>
                    <select name="category_id" 
                            class="w-full px-4 py-3 rounded-lg border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:border-[#7700F0] transition-all">
                        <option value="">Sin categoría</option>
                        @foreach(\App\Models\Category::all() as $category)
                            <option value="{{ $category->id }}" {{ $note->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Contenedor de Etiquetas Existentes e Invocador de Modal --}}
                <div class="mb-5 bg-slate-50/50 rounded-xl border border-dashed border-slate-200 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Etiquetas asignadas</span>
                            <div id="noteTagsContainer" class="flex flex-wrap gap-1.5 mt-1">
                                </div>
                        </div>
                        <button type="button" onclick="openAssignTagsModal()"
                                class="px-3 py-1.5 text-xs text-[#7700F0] border border-[#7700F0]/30 bg-white rounded-lg hover:bg-purple-50 focus:ring-2 focus:ring-[#7700F0]/20 transition-all font-medium flex items-center gap-1.5">
                            <i class="fa-solid fa-tags text-sm"></i> Administrar etiquetas
                        </button>
                    </div>
                </div>

                {{-- Campo: Contenido Markdown --}}
                <div class="mb-6 space-y-2">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Contenido (Markdown)</label>
                    <textarea name="content_markdown" rows="15" 
                              class="w-full p-4 rounded-lg border border-slate-200 bg-slate-50 font-mono text-sm text-slate-800 focus:outline-none focus:border-[#7700F0] focus:ring-1 focus:ring-[#7700F0]/30 transition-all" required>{{ old('content_markdown', $note->content_markdown) }}</textarea>
                </div>

                {{-- Modal de asignación de etiquetas --}}
                <div id="assignTagsModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center transition-all">
                    <div class="bg-white rounded-xl border border-[rgba(119,0,240,0.15)] shadow-2xl max-w-md w-full mx-4 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex items-center gap-2.5 text-slate-900">
                            <i class="fa-solid fa-tags text-[#7700F0] text-lg"></i>
                            <h3 class="text-lg font-bold">Asignar etiquetas</h3>
                        </div>
                        <div class="p-6">
                            <div id="allTagsList" class="space-y-1.5 max-h-64 overflow-y-auto pr-1">
                                </div>
                            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                                <button type="button" onclick="closeAssignTagsModal()" 
                                        class="px-4 py-2 border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 rounded-lg text-sm font-medium transition-all">
                                    Cancelar
                                </button>
                                <button type="button" onclick="saveAssignedTags()" 
                                        class="px-4 py-2 bg-[#7700F0] hover:bg-[#7d2eff] text-white rounded-lg text-sm font-medium shadow-md shadow-[#7700F0]/20 transition-all">
                                    Confirmar Asignación
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Lógica de JavaScript sincronizada con los nuevos estilos e iconos --}}
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
                            container.innerHTML = `<span class="text-xs text-slate-400 italic">Sin etiquetas asignadas</span>`;
                            return;
                        }
                        container.innerHTML = assignedTags.map(tag => 
                            `<span class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md text-xs font-medium border border-slate-200">
                                <i class="fa-solid fa-tag text-[#7700F0]/60 text-[10px]"></i> ${tag.name}
                            </span>`
                        ).join('');
                    }
            
                    function openAssignTagsModal() {
                        fetch('{{ route("tags.index") }}')
                            .then(response => response.json())
                            .then(tags => {
                                allTags = tags;
                                const container = document.getElementById('allTagsList');
                                container.innerHTML = allTags.map(tag => `
                                    <label class="flex items-center gap-3 p-2.5 hover:bg-purple-50/50 rounded-lg cursor-pointer border border-transparent hover:border-[#7700F0]/10 transition-all group">
                                        <input type="checkbox" value="${tag.id}" 
                                            ${assignedTags.some(t => t.id === tag.id) ? 'checked' : ''}
                                            class="w-4 h-4 rounded border-slate-300 text-[#7700F0] focus:ring-[#7700F0]/30 transition-all">
                                        <div class="flex items-center gap-2 text-sm font-medium text-slate-700 group-hover:text-slate-900">
                                            <i class="fa-solid fa-tag text-slate-300 group-hover:text-[#7700F0]/50 transition-colors text-xs"></i>
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