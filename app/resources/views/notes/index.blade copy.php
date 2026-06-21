<x-app-layout>
    <div class="p-6 pt-16 lg:pt-6 max-w-7xl mx-auto space-y-8">
        
        {{-- ============================================ --}}
        {{-- TARJETAS DE ESTADÍSTICAS --}}
        {{-- ============================================ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Tarjeta: Total Notas --}}
            <div class="bg-white rounded-xl border border-[rgba(119,0,240,0.15)] shadow-sm p-6 transition-all duration-300 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Notas</p>
                        <p class="text-3xl font-bold text-slate-800 tracking-tight">{{ $totalNotas }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-[#7700F0]/10 flex items-center justify-center text-[#7700F0]">
                        <i class="fa-solid fa-note-sticky text-xl"></i>
                    </div>
                </div>
            </div>
            {{-- Tarjeta: Total Categorías --}}
            <div class="bg-white rounded-xl border border-[rgba(119,0,240,0.15)] shadow-sm p-6 transition-all duration-300 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Categorías</p>
                        <p class="text-3xl font-bold text-slate-800 tracking-tight">{{ $totalCategorias }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-[#7700F0]/10 flex items-center justify-center text-[#7700F0]">
                        <i class="fa-solid fa-folder text-xl"></i>
                    </div>
                </div>
            </div>
        </div>


        {{-- ============================================ --}}
        {{-- BARRA DE ACCIONES Y CONTROLES (ACTUALIZADA) --}}
        {{-- ============================================ --}}
        <div class="bg-white rounded-xl border border-[rgba(119,0,240,0.12)] shadow-sm p-4 flex flex-wrap gap-3 items-center justify-between">
            {{-- Grupo Izquierdo: Filtros avanzados actualizados --}}
            <div class="flex flex-wrap gap-2.5">
                <a href="{{ route('notes.index') }}" 
                   class="px-4 py-2 border border-[#7700F0]/20 text-slate-700 bg-white hover:bg-[#7700F0]/10 hover:text-[#7700F0] rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-list text-xs"></i> Listar todas
                </a>
                
                {{-- Botón ajustado a "Filtrar por categorías" --}}
                <button id="filterBtn" 
                        class="px-4 py-2 border border-[#7700F0]/20 text-slate-700 bg-white hover:bg-[#7700F0]/10 hover:text-[#7700F0] rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-folder-open text-xs"></i> Filtrar por categorías
                </button>

                {{-- Nueva opción: Filtrar por etiquetas --}}
                <button id="filterTagsBtn" 
                        class="px-4 py-2 border border-[#7700F0]/20 text-slate-700 bg-white hover:bg-[#7700F0]/10 hover:text-[#7700F0] rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-tags text-xs"></i> Filtrar por etiquetas
                </button>
            </div>

            {{-- Grupo Derecho: Acciones de Sincronización y Creación (Se mantiene igual) --}}
            <div class="flex flex-wrap gap-2.5">
                <form method="POST" action="{{ route('categories.recalculate') }}" class="inline"
                    onsubmit="return confirm('¿Recalcular categorías? Esto detectará directorios nuevos y huérfanos. ¿Continuar?')">
                    @csrf
                    <button type="submit" 
                            class="px-3 py-2 border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 active:scale-95" title="Recalcular Categorías">
                        <i class="fa-solid fa-folder-tree text-slate-400"></i> <span class="hidden sm:inline">Estructura</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('notes.sync') }}" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 border border-slate-200 text-slate-700 bg-white hover:border-[#7700F0]/30 hover:bg-purple-50/50 hover:text-[#7700F0] rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 active:scale-95">
                        <i class="fa-solid fa-rotate text-slate-400 animate-hover"></i> Sincronizar
                    </button>
                </form>

                <button id="uploadNoteBtn" 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-md shadow-emerald-600/10 transition-all duration-200 flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-upload"></i> Subir nota
                </button>

                <a href="{{ route('notes.create') }}" 
                   class="px-4 py-2 bg-[#7700F0] hover:bg-[#8616ff] text-white rounded-lg text-sm font-medium shadow-md shadow-[#7700F0]/20 transition-all duration-200 flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-plus"></i> Nueva nota
                </a>
            </div>
        </div>

        
        {{-- ============================================ --}}
        {{-- LISTADO DE NOTAS --}}
        {{-- ============================================ --}}
        <div class="bg-white rounded-xl border border-[rgba(119,0,240,0.15)] shadow-sm overflow-hidden">
            @if($notes->isEmpty())
                <div class="p-16 text-center text-slate-400 space-y-3">
                    <i class="fa-solid fa-folder-open text-4xl text-slate-200"></i>
                    <p class="text-sm italic">No hay notas disponibles en esta sección.</p>
                </div>
            @else
                @php
                    $currentFilter = session('last_notes_filter');
                    $queryParams = $currentFilter ? ['category' => $currentFilter, 'page' => $notes->currentPage()] : ['page' => $notes->currentPage()];
                @endphp

                <ul class="divide-y divide-slate-100 w-full">
                    @foreach($notes as $note)
                        @php
                            $rowClass = 'hover:bg-slate-50/50';
                            if($note->status === 'orphan_category') {
                                $rowClass = 'bg-amber-50/40 border-l-4 border-amber-400 hover:bg-amber-50/70';
                            } elseif($note->status === 'modified') {
                                $rowClass = 'bg-orange-50/40 border-l-4 border-orange-400 hover:bg-orange-50/70';
                            } elseif($note->status === 'uncategorized') {
                                $rowClass = 'bg-slate-50/40 border-l-4 border-slate-400 hover:bg-slate-50/70';
                            }
                        @endphp
                        
                        {{-- Corrección: w-full e items-stretch para abarcar todo el ancho real --}}
                        <li class="p-4 {{ $rowClass }} w-full flex flex-col sm:flex-row sm:items-stretch justify-between gap-4 transition-colors duration-150">
                            
                            {{-- Contenedor de información: flex-1 para expandirse y empujar los botones al extremo --}}
                            <div class="flex-1 min-w-0 space-y-1">
                                <a href="{{ route('notes.show', array_merge(['note' => $note], $queryParams)) }}" 
                                   class="text-base font-medium text-slate-800 hover:text-[#7700F0] transition-colors tracking-tight block truncate">
                                    {{ $note->title }}
                                </a>
                                <div class="text-xs font-medium flex flex-wrap items-center gap-2">
                                    @if($note->status === 'orphan_category')
                                        <span class="text-amber-600 flex items-center gap-1"><i class="fa-solid fa-triangle-exclamation"></i> Categoría '{{ $note->category?->name ?? '?' }}' ya no existe</span>
                                    @elseif($note->status === 'modified')
                                        <span class="text-orange-600 flex items-center gap-1"><i class="fa-solid fa-pen-nib"></i> Contenido modificado en disco</span>
                                    @elseif($note->status === 'uncategorized')
                                        <span class="text-slate-500 flex items-center gap-1"><i class="fa-solid fa-file-dashed"></i> Sin categoría asignada</span>
                                    @else
                                        <span class="text-slate-400 flex items-center gap-1">
                                            <i class="fa-solid fa-folder text-slate-300 text-[11px]"></i> {{ $note->category?->name ?? 'Sin categoría' }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Listado interno de etiquetas --}}
                                @if($note->tags->count() > 0)
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        @foreach($note->tags as $tag)
                                            <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[11px] font-medium border border-slate-200/60">
                                                <i class="fa-solid fa-tag text-[#7700F0]/50 text-[9px]"></i> {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Botones de Fila: Alineados permanentemente a la derecha y centrados verticalmente --}}
                            <div class="flex items-center gap-1.5 justify-end self-end sm:self-auto shrink-0">
                                <a href="{{ route('notes.edit', array_merge(['note' => $note], $queryParams)) }}" 
                                   class="w-8 h-8 rounded-lg border border-slate-200 bg-white hover:border-[#7700F0]/30 hover:bg-purple-50/50 hover:text-[#7700F0] flex items-center justify-center transition-all duration-150 text-slate-400" title="Editar">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </a>
                                <form method="POST" action="{{ route('notes.destroy', $note) }}" onsubmit="return confirm('¿Eliminar esta nota?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-8 h-8 rounded-lg border border-slate-200 bg-white hover:border-red-200 hover:bg-red-50 hover:text-red-600 flex items-center justify-center transition-all duration-150 text-slate-400" title="Eliminar">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- Paginador --}}
            @if($notes->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    <div class="flex items-center gap-1">
                        {{ $notes->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>

        {{-- ============================================ --}}
        {{-- MODAL: FILTRAR POR CATEGORÍA --}}
        {{-- ============================================ --}}
        <div id="filterModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center transition-all">
            <div class="bg-white rounded-xl border border-[rgba(119,0,240,0.15)] shadow-2xl max-w-md w-full mx-4 overflow-hidden flex flex-col">
                <div class="p-6 border-b border-slate-100 flex items-center gap-2.5 text-slate-900">
                    <i class="fa-solid fa-filter text-[#7700F0] text-lg"></i>
                    <h3 class="text-xl font-bold tracking-tight">Filtrar por categoría</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="space-y-1.5 overflow-y-auto max-h-64 pr-1">
                        <a href="{{ route('notes.filter') }}?page={{ $notes->currentPage() }}" 
                           class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-purple-50/50 text-slate-700 hover:text-[#7700F0] font-medium text-sm rounded-lg transition-all border border-transparent hover:border-[#7700F0]/10">
                            <i class="fa-solid fa-border-all text-xs opacity-60"></i> Todas las notas
                        </a>
                        @foreach(\App\Models\Category::all() as $category)
                            <div class="flex items-center justify-between group/row">
                                <a href="{{ route('notes.filter', $category->slug) }}?page={{ $notes->currentPage() }}" 
                                   class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-purple-50/50 text-slate-700 hover:text-[#7700F0] font-medium text-sm rounded-lg flex-1 transition-all border border-transparent hover:border-[#7700F0]/10">
                                    <i class="fa-solid fa-folder text-xs opacity-40 group-hover/row:text-[#7700F0] group-hover/row:opacity-100 transition-all"></i> {{ $category->name }}
                                </a>
                                <form method="POST" action="{{ route('categories.destroy', $category) }}" 
                                    onsubmit="return confirm('¿Eliminar categoría \"{{ $category->name }}\"?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-red-600 px-3 py-2 transition-colors" title="Eliminar categoría">
                                        <i class="fa-solid fa-xmark text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                    <div class="pt-4 border-t border-slate-100 text-right">
                        <button onclick="closeModal()" 
                                class="px-4 py-2 border border-[#7700F0]/20 text-slate-700 bg-white hover:bg-[#7700F0]/10 hover:text-[#7700F0] rounded-lg text-sm font-medium transition-all duration-200">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- MODAL: SUBIR NOTA --}}
        {{-- ============================================ --}}
        <div id="uploadModal" style="display: none;" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center transition-all">
            <div class="bg-white rounded-xl border border-[rgba(119,0,240,0.15)] shadow-2xl max-w-lg w-full mx-4 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center gap-2.5 text-slate-900">
                    <i class="fa-solid fa-cloud-arrow-up text-emerald-600 text-lg"></i>
                    <h3 class="text-xl font-bold tracking-tight">Subir nota externa</h3>
                </div>
                
                <form id="uploadForm" method="POST" action="{{ route('notes.upload') }}" enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf
                    
                    {{-- Selección de archivo --}}
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Archivo Markdown (.md) *</label>
                        <input type="file" name="file" accept=".md" required
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border file:border-slate-200 file:text-xs file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-purple-50 hover:file:text-[#7700F0] hover:file:border-[#7700F0]/30 file:transition-all cursor-pointer">
                        <p class="text-[11px] text-slate-400">El sistema procesará la metadata y el Front Matter del archivo.</p>
                    </div>
                    
                    {{-- Categorías existentes --}}
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Categoría existente (opcional)</label>
                        <select name="category_id" class="w-full px-4 py-3 rounded-lg border border-slate-200 bg-slate-50/80 text-sm text-slate-800 focus:outline-none focus:border-[#7700F0] focus:ring-4 focus:ring-[#7700F0]/15 transition-all duration-200">
                            <option value="">-- Ninguna, usar "General" o la nueva --</option>
                            @foreach(\App\Models\Category::all() as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Nueva categoría --}}
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nueva categoría (opcional)</label>
                        <input type="text" name="new_category" class="w-full px-4 py-3 rounded-lg border border-slate-200 bg-slate-50/80 text-sm text-slate-800 focus:outline-none focus:border-[#7700F0] focus:ring-4 focus:ring-[#7700F0]/15 transition-all duration-200" 
                            placeholder="Ej: Laravel, Docker, GitOps">
                        <p class="text-[11px] text-slate-400">Si el campo tiene texto, se priorizará la creación de esta categoría automáticamente.</p>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeUploadModal()" 
                                class="px-4 py-2 border border-[#7700F0]/20 text-slate-700 bg-white hover:bg-[#7700F0]/10 hover:text-[#7700F0] rounded-lg text-sm font-medium transition-all duration-200">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-md shadow-emerald-600/10 transition-all duration-200">
                            Procesar y Subir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- SCRIPTS --}}
    {{-- ============================================ --}}
    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                
                // MODAL: Subir nota
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
                
                // MODAL: Filtrar
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