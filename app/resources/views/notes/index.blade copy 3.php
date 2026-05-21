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

        {{-- Modal Subir Nota --}}
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
                                <option value="">-- Por defecto: General --</option>
                                @foreach(\App\Models\Category::all() as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                                {{-- <option value="new">+ Crear nueva categoría</option> --}}
                            </select>
                            {{-- remove this block  --}}
                            {{-- <select name="category_id" id="categorySelect" class="w-full border-gray-300 rounded-lg">
                                <option value="">-- Seleccionar categoría --</option>
                                @foreach(\App\Models\Category::all() as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                                <option value="new">+ Crear nueva categoría</option>
                            </select> --}}
                            {{-- remove block until here --}}
                        </div>
                        
                        {{-- <div class="mb-4 hidden" id="newCategoryDiv">
                            <label class="block text-gray-700 font-medium mb-2">Nueva categoría</label>
                            <input type="text" name="new_category" class="w-full border-gray-300 rounded-lg" placeholder="Ej: Laravel">
                        </div>
                         --}}

                        {{-- <div class="mb-4" id="newCategoryDiv">
                            <label class="block text-gray-700 font-medium mb-2">O crear nueva categoría</label>
                            <input type="text" name="new_category" class="w-full border-gray-300 rounded-lg" placeholder="Escribe el nombre de una nueva categoría (opcional)">
                            <p class="text-xs text-gray-500 mt-1">Si escribes una nueva categoría, se creará automáticamente</p>
                        </div> --}}
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Nueva categoría (opcional)</label>
                            <input type="text" name="new_category" class="w-full border-gray-300 rounded-lg" placeholder="Escribe una nueva categoría. Si la escribes, se creará automáticamente.">
                            <p class="text-xs text-gray-500 mt-1">Deja en blanco si no quieres crear una nueva categoría.</p>
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
    // Función para inicializar el comportamiento del select
    function initCategorySelect() {
        var select = document.getElementById('categorySelect');
        var div = document.getElementById('newCategoryDiv');
        
        if (select && div) {
            // Remover eventos previos para evitar duplicados
            var newSelect = select.cloneNode(true);
            select.parentNode.replaceChild(newSelect, select);
            
            // Actualizar referencia
            var finalSelect = document.getElementById('categorySelect');
            
            // Agregar evento change
            finalSelect.addEventListener('change', function() {
                if (this.value === 'new') {
                    div.classList.remove('hidden');
                } else {
                    div.classList.add('hidden');
                }
            });
            
            // Verificar estado inicial
            if (finalSelect.value === 'new') {
                div.classList.remove('hidden');
            } else {
                div.classList.add('hidden');
            }
        }
    }
    
    // Inicializar cuando el DOM cargue
    document.addEventListener('DOMContentLoaded', initCategorySelect);
    
    // También inicializar cuando se abra el modal (por si acaso)
    var uploadModal = document.getElementById('uploadModal');
    var uploadBtn = document.getElementById('uploadNoteBtn');
    
    if (uploadBtn && uploadModal) {
        uploadBtn.onclick = function(e) {
            e.preventDefault();
            uploadModal.style.display = 'flex';
            // Reinicializar el select cuando se abre el modal
            setTimeout(initCategorySelect, 50);
        };
    }
    
    // Función para cerrar modal
    window.closeUploadModal = function() {
        var modal = document.getElementById('uploadModal');
        if (modal) modal.style.display = 'none';
        var form = document.getElementById('uploadForm');
        if (form) form.reset();
        var div = document.getElementById('newCategoryDiv');
        if (div) div.classList.add('hidden');
    };
    
    // Cerrar modal al hacer clic fuera
    if (uploadModal) {
        uploadModal.onclick = function(e) {
            if (e.target === uploadModal) window.closeUploadModal();
        };
    }
    </script>
        <script>
            /**
             * ============================================================
             * DKNOTES - Funciones de interfaz
             * ============================================================
             * Este archivo controla:
             * - Apertura/cierre del modal "Subir nota"
             * - Apertura/cierre del modal "Filtrar"
             * - Mostrar/ocultar campo de "Nueva categoría"
             * - Comportamiento responsivo de los modales
             * ============================================================
             */

            // Esperar a que el DOM esté completamente cargado
            document.addEventListener('DOMContentLoaded', function() {
                
                // ============================================
                // MODAL: Subir nota
                // ============================================
                var uploadBtn = document.getElementById('uploadNoteBtn');
                var uploadModal = document.getElementById('uploadModal');
                var closeUploadModalBtn = document.getElementById('closeUploadModalBtn');
                
                // Abrir modal
                if (uploadBtn && uploadModal) {
                    uploadBtn.onclick = function(e) {
                        e.preventDefault();
                        uploadModal.style.display = 'flex';
                        console.log('Modal subir nota abierto');
                    };
                }
                
                // Función global para cerrar modal
                window.closeUploadModal = function() {
                    if (uploadModal) uploadModal.style.display = 'none';
                    var form = document.getElementById('uploadForm');
                    if (form) form.reset();
                    var newCatDiv = document.getElementById('newCategoryDiv');
                    if (newCatDiv) newCatDiv.classList.add('hidden');
                    console.log('Modal subir nota cerrado');
                };
                
                // Cerrar al hacer clic fuera del modal
                if (uploadModal) {
                    uploadModal.onclick = function(e) {
                        if (e.target === uploadModal) window.closeUploadModal();
                    };
                }
                
                // ============================================
                // CAMPO: Nueva categoría (mostrar/ocultar)
                // ============================================
                var categorySelect = document.getElementById('categorySelect');
                var newCategoryDiv = document.getElementById('newCategoryDiv');
                
                if (categorySelect && newCategoryDiv) {
                    categorySelect.onchange = function() {
                        if (this.value === 'new') {
                            newCategoryDiv.classList.remove('hidden');
                            console.log('Campo nueva categoría visible');
                        } else {
                            newCategoryDiv.classList.add('hidden');
                            console.log('Campo nueva categoría oculto');
                        }
                    };
                }
                
                // ============================================
                // MODAL: Filtrar por categoría
                // ============================================
                var filterBtn = document.getElementById('filterBtn');
                var filterModal = document.getElementById('filterModal');
                
                if (filterBtn && filterModal) {
                    filterBtn.onclick = function() {
                        filterModal.classList.remove('hidden');
                        console.log('Modal filtrar abierto');
                    };
                }
                
                window.closeModal = function() {
                    if (filterModal) filterModal.classList.add('hidden');
                    console.log('Modal filtrar cerrado');
                };
                
                if (filterModal) {
                    filterModal.onclick = function(e) {
                        if (e.target === filterModal) window.closeModal();
                    };
                }
                
                console.log('✅ DKNOTES: Todos los scripts cargados correctamente');
            });
        </script>

        <script>
    // Función para activar el select de categoría
    function activarSelectCategoria() {
        var select = document.getElementById('categorySelect');
        var div = document.getElementById('newCategoryDiv');
        
        if (select && div) {
            // Remover evento anterior (si existe)
            var nuevoSelect = select.cloneNode(true);
            select.parentNode.replaceChild(nuevoSelect, select);
            
            // Agregar evento al nuevo select
            nuevoSelect.addEventListener('change', function() {
                if (this.value === 'new') {
                    div.classList.remove('hidden');
                    console.log('✅ Campo nueva categoría visible');
                } else {
                    div.classList.add('hidden');
                    console.log('❌ Campo nueva categoría oculto');
                }
            });
            
            // Verificar estado actual
            if (nuevoSelect.value === 'new') {
                div.classList.remove('hidden');
            } else {
                div.classList.add('hidden');
            }
        } else {
            console.log('⚠️ Select o div no encontrado');
        }
    }
    
    // Inicializar cuando el DOM cargue
    document.addEventListener('DOMContentLoaded', activarSelectCategoria);
    
    // Reinicializar cuando se abre el modal
    var uploadBtn = document.getElementById('uploadNoteBtn');
    var uploadModal = document.getElementById('uploadModal');
    
    if (uploadBtn && uploadModal) {
        uploadBtn.addEventListener('click', function() {
            uploadModal.style.display = 'flex';
            setTimeout(activarSelectCategoria, 50);
        });
    }
</script>

</x-app-layout>