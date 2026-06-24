<x-app-layout>
    <div class="p-6 pt-16 lg:pt-6 max-w-7xl mx-auto space-y-8 page-daten">
     


        <!-- Stats here -->
 
        
        <!-- end stats here -->
            
        

        
        

        {{-- ============================================ --}}
        {{-- BARRA DE ACCIONES Y CONTROLES --}}
        {{-- ============================================ --}}
        <div class="bg-daten-card rounded-xl border border-daten shadow-sm p-4 flex flex-wrap gap-3 items-center justify-between">
            {{-- Grupo Izquierdo: Filtros avanzados --}}
            <div class="flex flex-wrap gap-2.5">
                <a href="{{ route('notes.index') }}" 
                   class="px-4 py-2 border border-daten text-daten-secondary bg-daten-card hover:bg-brand-glow/10 hover:text-brand-glow rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-list text-xs"></i> Listar todas
                </a>
                
                <button id="filterBtn" 
                        class="px-4 py-2 border border-daten text-daten-secondary bg-daten-card hover:bg-brand-glow/10 hover:text-brand-glow rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-folder-open text-xs"></i> Filtrar por categorías
                </button>

                <button id="filterTagsBtn" 
                        class="px-4 py-2 border border-daten text-daten-secondary bg-daten-card hover:bg-brand-glow/10 hover:text-brand-glow rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-tags text-xs"></i> Filtrar por etiquetas
                </button>
            </div>

            {{-- Grupo Derecho: Acciones --}}
            <div class="flex flex-wrap gap-2.5">
                <form method="POST" action="{{ route('categories.recalculate') }}" class="inline"
                    onsubmit="return confirm('¿Recalcular categorías? Esto detectará directorios nuevos y huérfanos. ¿Continuar?')">
                    @csrf
                    <button type="submit" 
                            class="px-3 py-2 border border-daten text-daten-secondary bg-daten-card hover:bg-brand-glow/10 hover:text-brand-glow rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 active:scale-95" title="Recalcular Categorías">
                        <i class="fa-solid fa-folder-tree text-daten-muted"></i> <span class="hidden sm:inline">Estructura</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('notes.sync') }}" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 border border-daten text-daten-secondary bg-daten-card hover:bg-brand-glow/10 hover:text-brand-glow rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 active:scale-95">
                        <i class="fa-solid fa-rotate text-daten-muted"></i> Sincronizar
                    </button>
                </form>

                <button id="uploadNoteBtn" 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-md shadow-emerald-600/10 transition-all duration-200 flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-upload"></i> Subir nota
                </button>

                <a href="{{ route('notes.create') }}" 
                   class="px-4 py-2 bg-brand-glow hover:bg-brand-accent text-white rounded-lg text-sm font-medium shadow-md shadow-brand-glow/20 transition-all duration-200 flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-plus"></i> Nueva nota
                </a>
            </div>
            <!-- Bloque de barra de acciones  -->
        </div>

        
        {{-- ============================================ --}}
        {{-- LISTADO DE NOTAS --}}
        {{-- ============================================ --}}
        <div class="bg-daten-card rounded-xl border border-daten shadow-sm overflow-hidden">
            @if($notes->isEmpty())
                <div class="p-16 text-center text-daten-muted space-y-3">
                    <i class="fa-solid fa-folder-open text-4xl text-daten-muted/30"></i>
                    <p class="text-sm italic">No hay notas disponibles en esta sección.</p>
                </div>
            @else
                @php
                    $currentFilter = session('last_notes_filter');
                    $queryParams = $currentFilter ? ['category' => $currentFilter, 'page' => $notes->currentPage()] : ['page' => $notes->currentPage()];
                @endphp

                <ul class="divide-y divide-daten w-full">
                    @foreach($notes as $note)
                        @php
                            $rowClass = 'hover:bg-brand-glow/5 transition-colors duration-150';
                            if($note->status === 'orphan_category') {
                                $rowClass = 'bg-amber-50/40 dark:bg-amber-950/30 border-l-4 border-amber-400 dark:border-amber-600 hover:bg-amber-50/70 dark:hover:bg-amber-950/50 transition-colors duration-150';
                            } elseif($note->status === 'modified') {
                                $rowClass = 'bg-orange-50/40 dark:bg-orange-950/30 border-l-4 border-orange-400 dark:border-orange-600 hover:bg-orange-50/70 dark:hover:bg-orange-950/50 transition-colors duration-150';
                            } elseif($note->status === 'uncategorized') {
                                $rowClass = 'bg-slate-50/40 dark:bg-slate-800/30 border-l-4 border-slate-400 dark:border-slate-600 hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors duration-150';
                            }
                        @endphp
                        
                        <li class="p-4 {{ $rowClass }} w-full flex flex-col sm:flex-row sm:items-stretch justify-between gap-4">
                            
                            {{-- Contenedor de información --}}
                            <div class="flex-1 min-w-0 space-y-1">
                                <a href="{{ route('notes.show', array_merge(['note' => $note], $queryParams)) }}" 
                                   class="text-base font-medium text-daten-primary hover:text-brand-glow transition-colors tracking-tight block truncate">
                                    {{ $note->title }}
                                </a>
                                <div class="text-xs font-medium flex flex-wrap items-center gap-2">
                                    @if($note->status === 'orphan_category')
                                        <span class="text-amber-600 dark:text-amber-400 flex items-center gap-1"><i class="fa-solid fa-triangle-exclamation"></i> Categoría '{{ $note->category?->name ?? '?' }}' ya no existe</span>
                                    @elseif($note->status === 'modified')
                                        <span class="text-orange-600 dark:text-orange-400 flex items-center gap-1"><i class="fa-solid fa-pen-nib"></i> Contenido modificado en disco</span>
                                    @elseif($note->status === 'uncategorized')
                                        <span class="text-daten-secondary flex items-center gap-1"><i class="fa-solid fa-file-dashed"></i> Sin categoría asignada</span>
                                    @else
                                        <span class="text-daten-muted flex items-center gap-1">
                                            <i class="fa-solid fa-folder text-daten-muted/50 text-[11px]"></i> {{ $note->category?->name ?? 'Sin categoría' }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Listado interno de etiquetas --}}
                                <!-- @if($note->tags->count() > 0)
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        @foreach($note->tags as $tag)
                                            <span class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded text-[11px] font-medium border border-slate-200/60 dark:border-slate-700/60">
                                                <i class="fa-solid fa-tag text-brand-glow/50 text-[9px]"></i> {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif -->
                                @if($note->tags->count() > 0)
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        @foreach($note->tags as $tag)
                                            @php
                                                $color = \App\Helpers\TagColors::getForTag($tag->name);
                                            @endphp
                                            <span class="inline-flex items-center gap-1 {{ $color['bg'] }} {{ $color['text'] }} px-2 py-0.5 rounded text-[11px] font-medium border {{ $color['border'] }}">
                                                <i class="fa-solid fa-tag text-[11px] opacity-80"></i>
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
        
                                
                        
                            </div>

                            {{-- Botones de Fila --}}
                            <div class="flex items-center gap-1.5 justify-end self-end sm:self-auto shrink-0">
                                <a href="{{ route('notes.edit', array_merge(['note' => $note], $queryParams)) }}" 
                                   class="w-8 h-8 rounded-lg border border-daten bg-daten-card hover:border-brand-glow/30 hover:bg-brand-glow/10 hover:text-brand-glow flex items-center justify-center transition-all duration-150 text-daten-muted" title="Editar">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </a>
                                <form method="POST" action="{{ route('notes.destroy', $note) }}" onsubmit="return confirm('¿Eliminar esta nota?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-8 h-8 rounded-lg border border-daten bg-daten-card hover:border-red-200 dark:hover:border-red-700 hover:bg-red-50 dark:hover:bg-red-950/30 hover:text-red-600 dark:hover:text-red-400 flex items-center justify-center transition-all duration-150 text-daten-muted" title="Eliminar">
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
                <div class="px-6 py-4 border-t border-daten bg-daten-input/50">
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
            <div class="bg-daten-card rounded-xl border border-daten shadow-2xl max-w-md w-full mx-4 overflow-hidden flex flex-col">
                <div class="p-6 border-b border-daten flex items-center gap-2.5 text-daten-primary">
                    <i class="fa-solid fa-filter text-brand-glow text-lg"></i>
                    <h3 class="text-xl font-bold tracking-tight">Filtrar por categoría</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="space-y-1.5 overflow-y-auto max-h-64 pr-1">
                        <a href="{{ route('notes.filter') }}?page={{ $notes->currentPage() }}" 
                           class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-brand-glow/5 text-daten-secondary hover:text-brand-glow font-medium text-sm rounded-lg transition-all border border-transparent hover:border-brand-glow/10">
                            <i class="fa-solid fa-border-all text-xs opacity-60"></i> Todas las notas
                        </a>
                        @foreach(\App\Models\Category::all() as $category)
                            <div class="flex items-center justify-between group/row">
                                <a href="{{ route('notes.filter', $category->slug) }}?page={{ $notes->currentPage() }}" 
                                   class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-brand-glow/5 text-daten-secondary hover:text-brand-glow font-medium text-sm rounded-lg flex-1 transition-all border border-transparent hover:border-brand-glow/10">
                                    <i class="fa-solid fa-folder text-xs opacity-40 group-hover/row:text-brand-glow group-hover/row:opacity-100 transition-all"></i> {{ $category->name }}
                                </a>
                                <form method="POST" action="{{ route('categories.destroy', $category) }}" 
                                    onsubmit="return confirm('¿Eliminar categoría \"{{ $category->name }}\"?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-daten-muted hover:text-red-600 dark:hover:text-red-400 px-3 py-2 transition-colors" title="Eliminar categoría">
                                        <i class="fa-solid fa-xmark text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                    <div class="pt-4 border-t border-daten text-right">
                        <button onclick="closeModal()" 
                                class="px-4 py-2 border border-daten text-daten-secondary bg-daten-card hover:bg-brand-glow/10 hover:text-brand-glow rounded-lg text-sm font-medium transition-all duration-200">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- MODAL: FILTRAR POR ETIQUETAS --}}      
        <div id="filterTagsModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center transition-all">
            <div class="bg-daten-card rounded-xl border border-daten shadow-2xl max-w-md w-full mx-4 overflow-hidden flex flex-col">
                <div class="p-6 border-b border-daten flex items-center gap-2.5 text-daten-primary">
                    <i class="fa-solid fa-tags text-brand-glow text-lg"></i>
                    <h3 class="text-xl font-bold tracking-tight">Filtrar por etiqueta</h3>
                </div>
                <div class="p-6 space-y-4">
                    {{-- Contenedor dinámico donde se cargarán las etiquetas --}}
                    <div id="modalTagsContainer" class="space-y-1.5 overflow-y-auto max-h-64 pr-1">
                        <div class="p-4 text-center text-daten-muted italic text-sm">Cargando etiquetas...</div>
                    </div>
                    <div class="pt-4 border-t border-daten text-right">
                        <button onclick="closeTagsModal()" 
                                class="px-4 py-2 border border-daten text-daten-secondary bg-daten-card hover:bg-brand-glow/10 hover:text-brand-glow rounded-lg text-sm font-medium transition-all duration-200">
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
            <div class="bg-daten-card rounded-xl border border-daten shadow-2xl max-w-lg w-full mx-4 overflow-hidden">
                <div class="p-6 border-b border-daten flex items-center gap-2.5 text-daten-primary">
                    <i class="fa-solid fa-cloud-arrow-up text-emerald-600 text-lg"></i>
                    <h3 class="text-xl font-bold tracking-tight">Subir nota externa</h3>
                </div>
                
                <form id="uploadForm" method="POST" action="{{ route('notes.upload') }}" enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf
                    
                    {{-- Selección de archivo --}}
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-daten-secondary uppercase tracking-wider">Archivo Markdown (.md) *</label>
                        <input type="file" name="file" accept=".md" required
                            class="w-full text-sm text-daten-secondary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border file:border-daten file:text-xs file:font-semibold file:bg-daten-input file:text-daten-secondary hover:file:bg-brand-glow/10 hover:file:text-brand-glow hover:file:border-brand-glow/30 file:transition-all cursor-pointer">
                        <p class="text-[11px] text-daten-muted">El sistema procesará la metadata y el Front Matter del archivo.</p>
                    </div>
                    
                    {{-- Categorías existentes --}}
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-daten-secondary uppercase tracking-wider">Categoría existente (opcional)</label>
                        <select name="category_id" class="w-full px-4 py-3 rounded-lg border border-daten bg-daten-input text-sm text-daten-primary focus:outline-none focus:border-brand-glow focus:ring-4 focus:ring-brand-glow/15 transition-all duration-200">
                            <option value="">-- Ninguna, usar "General" o la nueva --</option>
                            @foreach(\App\Models\Category::all() as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Nueva categoría --}}
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-daten-secondary uppercase tracking-wider">Nueva categoría (opcional)</label>
                        <input type="text" name="new_category" class="w-full px-4 py-3 rounded-lg border border-daten bg-daten-input text-sm text-daten-primary focus:outline-none focus:border-brand-glow focus:ring-4 focus:ring-brand-glow/15 transition-all duration-200" 
                            placeholder="Ej: Laravel, Docker, GitOps">
                        <p class="text-[11px] text-daten-muted">Si el campo tiene texto, se priorizará la creación de esta categoría automáticamente.</p>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-4 border-t border-daten">
                        <button type="button" onclick="closeUploadModal()" 
                                class="px-4 py-2 border border-daten text-daten-secondary bg-daten-card hover:bg-brand-glow/10 hover:text-brand-glow rounded-lg text-sm font-medium transition-all duration-200">
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

                // MODAL: Filtrar por Etiquetas
                var filterTagsBtn = document.getElementById('filterTagsBtn');
                var filterTagsModal = document.getElementById('filterTagsModal');
                var modalTagsContainer = document.getElementById('modalTagsContainer');

                if (filterTagsBtn && filterTagsModal) {
                    filterTagsBtn.onclick = function() {
                        filterTagsModal.classList.remove('hidden');
                        loadFilterTags(); // Carga las etiquetas al abrir el modal
                    };
                }

                window.closeTagsModal = function() {
                    if (filterTagsModal) filterTagsModal.classList.add('hidden');
                };

                if (filterTagsModal) {
                    filterTagsModal.onclick = function(e) {
                        if (e.target === filterTagsModal) window.closeTagsModal();
                    };
                }

                // Función para obtener dinámicamente las etiquetas que este usuario puede ver
                function loadFilterTags() {
                    fetch('{{ route("tags.index") }}')
                        .then(response => response.json())
                        .then(tags => {
                            modalTagsContainer.innerHTML = '';

                            // Enlace para limpiar el filtro de etiquetas
                            const allNotesLink = document.createElement('a');
                            allNotesLink.href = "{{ route('notes.index') }}";
                            allNotesLink.className = "flex items-center gap-2.5 px-4 py-2.5 hover:bg-brand-glow/5 text-daten-secondary hover:text-brand-glow font-medium text-sm rounded-lg transition-all border border-transparent hover:border-brand-glow/10";
                            allNotesLink.innerHTML = `<i class="fa-solid fa-border-all text-xs opacity-60"></i> Todas las notas (Sin filtrar)`;
                            modalTagsContainer.appendChild(allNotesLink);

                            if(tags.length === 0) {
                                modalTagsContainer.innerHTML += `<p class="p-4 text-center text-daten-muted italic text-xs">No tienes etiquetas disponibles.</p>`;
                                return;
                            }

                            // Renderizar cada etiqueta como un botón/enlace de filtro
                            tags.forEach(tag => {
                                const a = document.createElement('a');
                                // Redirige al filtro pasando el slug de la etiqueta
                                a.href = `{{ url('notes/filter-by-tag') }}/${tag.slug}`;
                                a.className = "flex items-center gap-2.5 px-4 py-2.5 hover:bg-brand-glow/5 text-daten-secondary hover:text-brand-glow font-medium text-sm rounded-lg transition-all border border-transparent hover:border-brand-glow/10 group/tagrow";
                                a.innerHTML = `
                                    <i class="fa-solid fa-tag text-xs opacity-40 group-hover/tagrow:text-brand-glow group-hover/tagrow:opacity-100 transition-all"></i>
                                    <span>${tag.name}</span>
                                `;
                                modalTagsContainer.appendChild(a);
                            });
                        })
                        .catch(error => {
                            modalTagsContainer.innerHTML = `<p class="p-4 text-center text-red-500 text-xs">Error al cargar las etiquetas.</p>`;
                        });
                }
            });
        })();
    </script>
</x-app-layout>