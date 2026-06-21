<x-app-layout>
    <div class="p-6 pt-16 lg:pt-6">
        {{-- ============================================ --}}
        {{-- TARJETAS DE ESTADÍSTICAS --}}
        {{-- ============================================ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Notas</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $totalNotas }}</p>
                    </div>
                    <div class="text-4xl text-gray-400">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Categorías</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $totalCategorias }}</p>
                    </div>
                    <div class="text-4xl text-gray-400">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- BOTONES DE ACCIÓN --}}
        {{-- ============================================ --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6 flex flex-wrap gap-3">
            <a href="{{ route('notes.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition flex items-center gap-2 text-gray-700">
                <i class="fa-solid fa-list-check"></i> Listar todas
            </a>
            <button id="filterBtn" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition flex items-center gap-2 text-gray-700">
                <i class="fa-solid fa-filter"></i> Filtrar
            </button>
            <button id="uploadNoteBtn" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition flex items-center gap-2">
                <i class="fa-solid fa-upload"></i> Subir nota
            </button>
            <form method="POST" action="{{ route('notes.sync') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
                    <i class="fa-solid fa-arrows-rotate"></i> Sincronizar
                </button>
            </form>
            <a href="{{ route('notes.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Nueva nota
            </a>
            <form method="POST" action="{{ route('categories.recalculate') }}" class="inline"
                onsubmit="return confirm('¿Recalcular categorías? Esto detectará directorios nuevos y huérfanos. ¿Continuar?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition flex items-center gap-2">
                    <i class="fa-solid fa-folder-tree"></i> Recalcular Categorías
                </button>
            </form>
        </div>

        {{-- ============================================ --}}
        {{-- LISTADO DE NOTAS --}}
        {{-- ============================================ --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if($notes->isEmpty())
                <div class="p-12 text-center text-gray-500">
                    No hay notas disponibles.
                </div>
            @else
                @php
                    $currentFilter = session('last_notes_filter');
                    $queryParams = $currentFilter ? ['category' => $currentFilter, 'page' => $notes->currentPage()] : ['page' => $notes->currentPage()];
                @endphp

                <ul class="divide-y divide-gray-200">
                    @foreach($notes as $note)
                      
                        @php
                            $rowClass = '';
                            switch($note->status) {
                                case 'orphan_category':
                                    $rowClass = 'bg-yellow-50 border-l-4 border-yellow-400';
                                    break;
                                case 'modified':
                                    $rowClass = 'bg-orange-50 border-l-4 border-orange-400';
                                    break;
                                case 'uncategorized':
                                    $rowClass = 'bg-gray-50 border-l-4 border-gray-400';
                                    break;
                                default:
                                    $rowClass = 'hover:bg-gray-50';
                            }
                        @endphp
                        
                        <li class="p-4 {{ $rowClass }} flex items-center justify-between">
                            <div class="flex-1">
                                <a href="{{ route('notes.show', array_merge(['note' => $note], $queryParams))  }}" class="text-gray-800 hover:text-blue-600 font-medium">
                                    {{ $note->title }}
                                </a>
                                <div class="text-sm mt-1 flex items-center gap-1.5">
                                    @if($note->status === 'orphan_category')
                                        <span class="text-yellow-600 flex items-center gap-1">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Categoría '{{ $note->category?->name ?? '?' }}' ya no existe
                                        </span>
                                    @elseif($note->status === 'modified')
                                        <span class="text-orange-600 flex items-center gap-1">
                                            <i class="fa-solid fa-rotate"></i> Contenido modificado en disco
                                        </span>
                                    @elseif($note->status === 'uncategorized')
                                        <span class="text-gray-500 flex items-center gap-1">
                                            <i class="fa-regular fa-file text-xs"></i> Sin categoría asignada
                                        </span>
                                    @else
                                        <span class="text-gray-500 text-sm flex items-center gap-1">
                                            <i class="fa-regular fa-folder text-xs"></i> {{ $note->category?->name ?? 'Sin categoría' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <a href="{{ route('notes.edit', array_merge(['note' => $note], $queryParams))}}" class="text-gray-400 hover:text-blue-600 transition" title="Editar">
                                    <i class="fa-solid fa-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('notes.destroy', $note) }}" onsubmit="return confirm('¿Eliminar esta nota?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition" title="Eliminar">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </li>

                        {{-- Mostrar etiqueta --}}
                        <div class="flex flex-wrap gap-1 px-4 pb-3">
                            @foreach($note->tags as $tag)
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded flex items-center gap-1">
                                    <i class="fa-solid fa-tag text-[10px] text-gray-400"></i> {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>

                    @endforeach
                </ul>
            @endif

            {{-- Paginador --}}
            @if($notes->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center gap-1">
                        {{ $notes->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>

        {{-- ============================================ --}}
        {{-- MODAL: FILTRAR POR CATEGORÍA --}}
        {{-- ============================================ --}}
        <div id="filterModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-4 flex flex-col">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Filtrar por categoría</h3>
                    <div class="space-y-2 overflow-y-auto max-h-64 pr-2">
                        <a href="{{ route('notes.filter') }}?page={{ $notes->currentPage() }}" class="block px-4 py-2 hover:bg-gray-100 rounded flex items-center gap-2 text-gray-700">
                            <i class="fa-solid fa-list-check text-gray-400"></i> Todas las notas
                        </a>
                        @foreach(\App\Models\Category::all() as $category)
                            <div class="flex items-center justify-between">
                                <a href="{{ route('notes.filter', $category->slug) }}?page={{ $notes->currentPage() }}" class="block px-4 py-2 hover:bg-gray-100 rounded flex-1 flex items-center gap-2 text-gray-700">
                                    <i class="fa-solid fa-folder text-gray-400"></i> {{ $category->name }}
                                </a>
                                <form method="POST" action="{{ route('categories.destroy', $category) }}" 
                                    onsubmit="return confirm('¿Eliminar categoría \"{{ $category->name }}\"?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600 px-2 transition" title="Eliminar categoría">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-right">
                        <button onclick="closeModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded transition text-gray-700">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- MODAL: SUBIR NOTA --}}
        {{-- ============================================ --}}
        <div id="uploadModal" style="display: none;" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-upload text-emerald-600"></i> Subir nota
                    </h3>
                    
                    <form id="uploadForm" method="POST" action="{{ route('notes.upload') }}" enctype="multipart/form-data">
                        @csrf
                        
                        {{-- Selección de archivo --}}
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Archivo Markdown (.md)</label>
                            <input type="file" name="file" accept=".md" required
                                class="w-full border-gray-300 rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">Selecciona un archivo .md de tu computadora</p>
                        </div>
                        
                        {{-- Categorías existentes (opcional) --}}
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Categoría existente (opcional)</label>
                            <select name="category_id" class="w-full border-gray-300 rounded-lg">
                                <option value="">-- Ninguna, usar "General" o la nueva --</option>
                                @foreach(\App\Models\Category::all() as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Nueva categoría (opcional) --}}
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Nueva categoría (opcional)</label>
                            <input type="text" name="new_category" class="w-full border-gray-300 rounded-lg" 
                                placeholder="Ej: Laravel, Docker, PHP">
                            <p class="text-xs text-gray-500 mt-1">
                                Si escribes una nueva categoría, se creará automáticamente. 
                                Si seleccionas una existente, se usará esa.
                            </p>
                        </div>
                        
                        <div class="flex justify-end gap-3">
                            <button type="button" onclick="closeUploadModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg transition text-gray-700">Cancelar</button>
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                                Subir nota
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- SCRIPTS --}}
    {{-- ============================================ --}}
    <script>
        /**
         * DKNOTES - Funciones de interfaz
         * Controla los modales de "Subir nota" y "Filtrar"
         */
        
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                
                // ============================================
                // MODAL: Subir nota
                // ============================================
                var uploadBtn = document.getElementById('uploadNoteBtn');
                var uploadModal = document.getElementById('uploadModal');
                
                if (uploadBtn && uploadModal) {
                    uploadBtn.onclick = function(e) {
                        e.preventDefault();
                        uploadModal.style.display = 'flex';
                    };
                }
                
                window.closeUploadModal = function() {
                    if (uploadModal) uploadModal.style.display = 'none';
                    var form = document.getElementById('uploadForm');
                    if (form) form.reset();
                };
                
                if (uploadModal) {
                    uploadModal.onclick = function(e) {
                        if (e.target === uploadModal) window.closeUploadModal();
                    };
                }
                
                // ============================================
                // MODAL: Filtrar
                // ============================================
                var filterBtn = document.getElementById('filterBtn');
                var filterModal = document.getElementById('filterModal');
                
                if (filterBtn && filterModal) {
                    filterBtn.onclick = function() {
                        filterModal.classList.remove('hidden');
                    };
                }
                
                window.closeModal = function() {
                    if (filterModal) filterModal.classList.add('hidden');
                };
                
                if (filterModal) {
                    filterModal.onclick = function(e) {
                        if (e.target === filterModal) window.closeModal();
                    };
                }
                
            });
        })();
    </script>
</x-app-layout>