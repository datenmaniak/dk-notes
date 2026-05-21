<x-app-layout>
    <div class="p-6 pt-16 lg:pt-6">
        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Notas</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $totalNotas }}</p>
                    </div>
                    <div class="text-4xl">📝</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Categorías</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $totalCategorias }}</p>
                    </div>
                    <div class="text-4xl">📂</div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6 flex flex-wrap gap-3">
            <a href="{{ route('notes.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition flex items-center gap-2">
                📋 Listar todas
            </a>
            <button id="filterBtn" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition flex items-center gap-2">
                🔍 Filtrar
            </button>

            <button id="uploadNoteBtn" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition flex items-center gap-2">
                📤 Subir nota
            </button>
           
            <form method="POST" action="{{ route('notes.sync') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
                    🔄 Sincronizar
                </button>
            </form>
            
            <a href="{{ route('notes.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg transition flex items-center gap-2">
                📝 Nueva nota
            </a>
            
            <form method="POST" action="{{ route('categories.recalculate') }}" class="inline"
                onsubmit="return confirm('¿Recalcular categorías? Esto detectará directorios nuevos y huérfanos. ¿Continuar?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition flex items-center gap-2">
                    📂 Recalcular Categorías
                </button>
            </form>
        </div>

        {{-- Notes List --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if($notes->isEmpty())
                <div class="p-12 text-center text-gray-500">
                    No hay notas disponibles.
                </div>
            @else
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
                                <a href="{{ route('notes.show', $note) }}" class="text-gray-800 hover:text-blue-600">
                                    {{ $note->title }}
                                </a>
                                <div class="text-sm mt-1">
                                    @if($note->status === 'orphan_category')
                                        <span class="text-yellow-600">⚠️ Categoría '{{ $note->category?->name ?? '?' }}' ya no existe</span>
                                    @elseif($note->status === 'modified')
                                        <span class="text-orange-600">🔄 Contenido modificado en disco</span>
                                    @elseif($note->status === 'uncategorized')
                                        <span class="text-gray-500">📄 Sin categoría asignada</span>
                                    @else
                                        <span class="text-gray-500 text-sm">
                                            {{ $note->category?->name ?? 'Sin categoría' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('notes.edit', $note) }}" class="text-gray-500 hover:text-blue-600" title="Editar">
                                    ✏️
                                </a>
                                <form method="POST" action="{{ route('notes.destroy', $note) }}" onsubmit="return confirm('¿Eliminar esta nota?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-500 hover:text-red-600" title="Eliminar">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </li>
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

        {{-- Modal Filtrar --}}
        <div id="filterModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Filtrar por categoría</h3>
                    <div class="space-y-2">
                        <a href="{{ route('notes.filter') }}" class="block px-4 py-2 hover:bg-gray-100 rounded">
                            📋 Todas las notas
                        </a>
                        @foreach(\App\Models\Category::all() as $category)
                            <div class="flex items-center justify-between">
                                <a href="{{ route('notes.filter', $category->slug) }}" class="block px-4 py-2 hover:bg-gray-100 rounded flex-1">
                                    📂 {{ $category->name }}
                                </a>
                                <form method="POST" action="{{ route('categories.destroy', $category) }}" 
                                    onsubmit="return confirm('¿Eliminar categoría \"{{ $category->name }}\"?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 px-2" title="Eliminar categoría">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-right">
                        <button onclick="closeModal()" class="px-4 py-2 bg-gray-200 rounded">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- remove desde aqui --}}
        {{-- Modal Subir Nota
        <div id="uploadModal" style="display: none;" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📤 Subir nota</h3>
                    
                    <form id="uploadForm" method="POST" action="{{ route('notes.upload') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Archivo Markdown (.md)</label>
                            <input type="file" name="file" accept=".md" required
                                class="w-full border-gray-300 rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">Selecciona un archivo .md de tu computadora</p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Categoría</label>
                            <select name="category_id" id="categorySelect" class="w-full border-gray-300 rounded-lg">
                                <option value="">-- Seleccionar categoría --</option>
                                @foreach(\App\Models\Category::all() as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                                <option value="new">+ Crear nueva categoría</option>
                            </select>
                        </div>
                        
                        <div class="mb-4 hidden" id="newCategoryDiv">
                            <label class="block text-gray-700 font-medium mb-2">Nueva categoría</label>
                            <input type="text" name="new_category" class="w-full border-gray-300 rounded-lg" placeholder="Ej: Laravel">
                        </div>
                        
                        <div class="flex justify-end gap-3">
                            <button type="button" onclick="closeUploadModal()" class="px-4 py-2 bg-gray-300 rounded-lg">Cancelar</button>
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">
                                Subir nota
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div> --}}
        {{-- remove hasta aqui --}}

        {{-- Modal Subir Nota --}}
        <div id="uploadModal" style="display: none;" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📤 Subir nota</h3>
                    
                    <form id="uploadForm" method="POST" action="{{ route('notes.upload') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Archivo Markdown (.md)</label>
                            <input type="file" name="file" accept=".md,.markdown" required
                                class="w-full border-gray-300 rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">Selecciona un archivo .md de tu computadora</p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Categoría</label>
                            <select name="category_id" id="categorySelect" class="w-full border-gray-300 rounded-lg">
                                <option value="">-- Seleccionar categoría --</option>
                                @foreach(\App\Models\Category::all() as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                                <option value="new">+ Crear nueva categoría</option>
                            </select>
                        </div>
                        
                        <div class="mb-4 hidden" id="newCategoryDiv">
                            <label class="block text-gray-700 font-medium mb-2">Nueva categoría</label>
                            <input type="text" name="new_category" class="w-full border-gray-300 rounded-lg" placeholder="Ej: Laravel">
                        </div>
                        
                        <div class="flex justify-end gap-3">
                            <button type="button" onclick="closeUploadModal()" class="px-4 py-2 bg-gray-300 rounded-lg">Cancelar</button>
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">
                                Subir nota
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            // Mostrar/ocultar campo de nueva categoría
            var catSelect = document.getElementById('categorySelect');
            var newCatDiv = document.getElementById('newCategoryDiv');
            
            if (catSelect && newCatDiv) {
                catSelect.addEventListener('change', function() {
                    if (this.value === 'new') {
                        newCatDiv.classList.remove('hidden');
                    } else {
                        newCatDiv.classList.add('hidden');
                    }
                });
            }
            
            // Función para cerrar modal
            window.closeUploadModal = function() {
                var modal = document.getElementById('uploadModal');
                if (modal) modal.style.display = 'none';
                var form = document.getElementById('uploadForm');
                if (form) form.reset();
                if (newCatDiv) newCatDiv.classList.add('hidden');
            };
        </script>

    </div>

    {{-- Scripts --}}
    <script>
        // ============================================
        // DEBUG: Verificar elementos
        // ============================================
        (function() {
            console.log('=== INICIO DEBUG ===');
            var btn = document.getElementById('uploadNoteBtn');
            var modal = document.getElementById('uploadModal');
            console.log('Botón uploadNoteBtn:', btn);
            console.log('Modal uploadModal:', modal);
            
            if (btn) {
                console.log('✅ Botón encontrado');
                btn.onclick = function(e) {
                    e.preventDefault();
                    console.log('🔘 Botón clickeado');
                    if (modal) {
                        modal.style.display = 'flex';
                        console.log('Modal abierto');
                    } else {
                        console.log('❌ Modal no encontrado');
                    }
                };
            } else {
                console.log('❌ Botón NO encontrado');
            }
        })();
        
        // ============================================
        // Funciones globales
        // ============================================
        window.closeUploadModal = function() {
            var modal = document.getElementById('uploadModal');
            if (modal) {
                modal.style.display = 'none';
                console.log('Modal cerrado');
            }
            var form = document.getElementById('uploadForm');
            if (form) form.reset();
            var newCatDiv = document.getElementById('newCategoryDiv');
            if (newCatDiv) newCatDiv.classList.add('hidden');
        };
        
        // Cerrar modal al hacer clic fuera
        var modal = document.getElementById('uploadModal');
        if (modal) {
            modal.onclick = function(e) {
                if (e.target === modal) {
                    window.closeUploadModal();
                }
            };
        }
        
        // Mostrar campo de nueva categoría
        var catSelect = document.getElementById('categorySelect');
        if (catSelect) {
            catSelect.onchange = function() {
                var newCatDiv = document.getElementById('newCategoryDiv');
                if (this.value === 'new') {
                    newCatDiv.classList.remove('hidden');
                } else {
                    newCatDiv.classList.add('hidden');
                }
            };
        }
        
        // Modal de filtrar
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
                if (e.target === filterModal) closeModal();
            };
        }
        
        console.log('=== FIN DEBUG ===');

        


    </script>
</x-app-layout>