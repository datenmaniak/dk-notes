<x-app-layout>
    <div class="p-6">
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
                        <li class="p-4 hover:bg-gray-50 flex items-center justify-between">
                            <a href="{{ route('notes.show', $note) }}" class="flex-1 text-gray-800 hover:text-blue-600">
                                {{ $note->title }}
                                <span class="text-sm text-gray-500 ml-2">
                                    {{ $note->category?->name ?? 'Sin categoría' }}
                                </span>
                            </a>
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
        </div>
    </div>

    {{-- Filter Modal (Simple) --}}
    <div id="filterModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
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