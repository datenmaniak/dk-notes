<x-app-layout>

     <!-- DIAGNÓSTICO: Valor de page = {{ $page ?? 'NO DEFINIDO' }} -->

    {{-- <div class="p-6 max-w-4xl mx-auto"> --}}
    <div class="p-6 pt-16 lg:pt-6 max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="mb-4">
                <a href="{{ route('notes.show', ['note' => $note, 'page' => $page ?? 1]) }}"
                class="text-blue-600 hover:underline">← Volver a la nota</a>
            </div>
            
            <h1 class="text-2xl font-bold mb-6">Editar Nota</h1>
            
       
            
            <form method="POST" action="{{ route('notes.update', $note) }}">
                @csrf
                
                {{-- Control de ubicacion del paginador --}}
                <input type="hidden" name="page" value="{{ $page ?? 1 }}">
                
                
                @method('PUT')
                
                <div class="flex gap-3 justify-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        💾 Guardar cambios
                    </button>
                    <a href="{{ route('notes.show', ['note' => $note, 'page' => $page ?? 1])  }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg">
                        Cancelar
                    </a>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Título</label>
                    <input type="text" name="title" value="{{ old('title', $note->title) }}" 
                           class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Categoría</label>
                    <select name="category_id" class="w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">Sin categoría</option>
                        @foreach(\App\Models\Category::all() as $category)
                            <option value="{{ $category->id }}" {{ $note->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                        {{-- 
                        <button type="button" onclick="openAssignTagsModal()" class="mb-2 text-sm text-[#7700F0] hover:text-purple-700 justify-end">
                        + Asignar etiqueta
                        </button> --}}

                    <div class="flex justify-end">
                        <button
                            type="button"
                            onclick="openAssignTagsModal()"
                            class="mb-2 ml-auto px-3 py-1.5 text-sm text-[#7700F0] border border-[#7700F0] bg-transparent rounded-md hover:bg-[#7700F0] hover:text-white transition-colors duration-200">
                            + Asignar etiqueta
                        </button>
                    </div>

                
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Contenido (Markdown)</label>
                    <textarea name="content_markdown" rows="15" 
                              class="w-full border-gray-300 rounded-lg font-mono text-sm focus:border-blue-500 focus:ring focus:ring-blue-200" required>{{ old('content_markdown', $note->content_markdown) }}</textarea>
                </div>
                
                {{-- movido al tope del form  --}}
                {{-- <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        💾 Guardar cambios
                    </button>
                    <a href="{{ route('notes.show', $note) }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg">
                        Cancelar
                    </a>
                </div> --}}

                <div class="mb-4">
                    {{-- <label class="block text-gray-700 font-medium mb-2">Etiquetas</label> --}}
                    {{-- <div id="noteTagsContainer" class="flex flex-wrap gap-2 mb-2"> --}}
                        <!-- Las etiquetas se mostrarán aquí -->
                   
                    {{-- Se ha movido arriba del contenido y aplicado nuevo estilos --}}
                    {{-- </div>
                        <button type="button" onclick="openAssignTagsModal()" class="text-sm text-[#7700F0] hover:text-purple-700">
                        + Asignar etiqueta
                        </button>
                    </div> --}}

                    {{-- Modal de asignación de etiquetas --}}
                    <div id="assignTagsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Asignar etiquetas</h3>
                                <div id="allTagsList" class="space-y-2 max-h-64 overflow-y-auto">
                                    <!-- Checkboxes se cargarán aquí -->
                                </div>
                                <div class="flex justify-end gap-3 mt-4">
                                    <button onclick="closeAssignTagsModal()" class="px-4 py-2 bg-gray-300 rounded-lg">Cancelar</button>
                                    <button onclick="saveAssignedTags()" class="px-4 py-2 bg-[#7700F0] text-white rounded-lg">Asignar</button>
                                </div>
                            </div>
                        </div>
                    </div>

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
                        container.innerHTML = assignedTags.map(tag => 
                            `<span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-sm">🏷️ ${tag.name}</span>`
                        ).join('');
                    }
            
                function openAssignTagsModal() {
                    // Cargar todas las etiquetas
                    fetch('{{ route("tags.index") }}')
                        .then(response => response.json())
                        .then(tags => {
                            allTags = tags;
                            const container = document.getElementById('allTagsList');
                            container.innerHTML = allTags.map(tag => `
                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded-lg">
                                    <input type="checkbox" value="${tag.id}" 
                                        ${assignedTags.some(t => t.id === tag.id) ? 'checked' : ''}
                                        class="rounded border-gray-300 text-[#7700F0]">
                                    <span>🏷️ ${tag.name}</span>
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