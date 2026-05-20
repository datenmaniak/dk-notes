<x-app-layout>
    <div class="p-6 pt-16 lg:pt-6 max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="mb-4">
                <a href="{{ route('notes.index') }}" class="text-blue-600 hover:underline">← Volver a mis notas</a>
            </div>
            
            <h1 class="text-2xl font-bold mb-6">Crear nueva nota</h1>
            
            <form method="POST" action="{{ route('notes.store') }}">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Título *</label>
                    <input type="text" name="title" value="{{ old('title') }}" 
                           class="w-full border-gray-300 rounded-lg focus:border-green-500 focus:ring focus:ring-green-200" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Categoría</label>
                    <select name="category_id" class="w-full border-gray-300 rounded-lg focus:border-green-500 focus:ring focus:ring-green-200">
                        <option value="">Sin categoría</option>
                        @foreach($categorias as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Contenido (Markdown) *</label>
                    <textarea name="content_markdown" rows="15" 
                              class="w-full border-gray-300 rounded-lg font-mono text-sm focus:border-green-500 focus:ring focus:ring-green-200" required>{{ old('content_markdown') }}</textarea>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                        💾 Crear nota
                    </button>
                    <a href="{{ route('notes.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>