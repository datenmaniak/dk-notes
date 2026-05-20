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
                📋 Listar
            </a>
            <button id="filterBtn" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition flex items-center gap-2">
                🔍 Filtrar
            </button>
            <form method="POST" action="{{ route('notes.sync') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
                    🔄 Sincronizar
                </button>
            </form>

            {{-- NUEVO BOTÓN --}}
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
            @if($notes->lastPage() > 1)
                <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">Mostrar:</span>
                        <select id="perPageSelect" class="px-2 py-1 border rounded-lg text-sm">
                            <option value="5" {{ request()->get('per_page', 10) == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ request()->get('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ request()->get('per_page', 10) == 20 ? 'selected' : '' }}>20</option>
                        </select>
                        <span class="text-sm text-gray-600">notas por página</span>
                    </div>
                    
                    <div class="flex items-center gap-1">
                        {{-- Botón anterior --}}
                        @if($notes->onFirstPage())
                            <span class="px-3 py-1 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed">« Anterior</span>
                        @else
                            <a href="{{ $notes->previousPageUrl() }}&per_page={{ request()->input('per_page', 5) }}" 
                            class="px-3 py-1 rounded-lg bg-gray-100 hover:bg-gray-200">« Anterior</a>
                        @endif
                        
                        {{-- Números de página --}}
                        @foreach(range(1, $notes->lastPage()) as $page)
                            @if($page == $notes->currentPage())
                                <span class="px-3 py-1 rounded-lg bg-[#7700F0] text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $notes->url($page) }}&per_page={{ request()->input('per_page', 5) }}" 
                                class="px-3 py-1 rounded-lg bg-gray-100 hover:bg-gray-200">{{ $page }}</a>
                            @endif
                        @endforeach
                        
                        {{-- Botón siguiente --}}
                        @if($notes->hasMorePages())
                            <a href="{{ $notes->nextPageUrl() }}&per_page={{ request()->input('per_page', 5) }}" 
                            class="px-3 py-1 rounded-lg bg-gray-100 hover:bg-gray-200">Siguiente »</a>
                        @else
                            <span class="px-3 py-1 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed">Siguiente »</span>
                        @endif
                    </div>
                </div>
            @endif

        </div>



    {{-- Filter Modal (Simple) --}}
    {{-- <div id="filterModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Filtrar por categoría</h3>
                <div class="space-y-2">
                    <a href="{{ route('notes.filter') }}" class="block px-4 py-2 hover:bg-gray-100 rounded">📋 Todas las notas</a>
                    @foreach(\App\Models\Category::all() as $category)
                        <a href="{{ route('notes.filter', $category->slug) }}" class="block px-4 py-2 hover:bg-gray-100 rounded">
                            📂 {{ $category->name }}
                        </a>
                    @endforeach
                </div>

                @foreach(\App\Models\Category::all() as $category)
                <div class="flex items-center justify-between">
                    <a href="{{ route('notes.filter', $category->slug) }}" class="block px-4 py-2 hover:bg-gray-100 rounded flex-1">
                        📂 {{ $category->name }}
                    </a>
                    <form method="POST" action="{{ route('categories.destroy', $category) }}" 
                        onsubmit="return confirm('¿Eliminar categoría \"{{ $category->name }}\"? Solo si no tiene notas.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 px-2" title="Eliminar categoría">🗑️</button>
                    </form>
                </div>
                @endforeach

                <div class="mt-4 text-right">
                    <button onclick="closeModal()" class="px-4 py-2 bg-gray-200 rounded">Cerrar</button>
                </div>
            </div>
        </div>
    </div> --}}

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

    <script>
        const modal = document.getElementById('filterModal');
        const filterBtn = document.getElementById('filterBtn');
        
        filterBtn.onclick = () => modal.classList.remove('hidden');
        
        function closeModal() {
            modal.classList.add('hidden');
        }
        
        modal.onclick = (e) => {
            if (e.target === modal) closeModal();
        };
    </script>
</x-app-layout>