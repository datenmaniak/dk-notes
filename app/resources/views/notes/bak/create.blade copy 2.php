<x-app-layout>
    <div class="p-6 pt-16 lg:pt-6 max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6 border border-[var(--border-brand)]">
            {{-- Enlace de retorno estilizado con Font Awesome --}}
            <div class="mb-5">
                <a href="{{ route('notes.index', ['page' => request()->input('page', 1)]) }}"
                   class="text-[#7700F0] hover:text-[#8616ff] font-medium text-sm flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Volver a mis notas
                </a>
            </div>
            
            <h1 class="text-2xl font-bold mb-6 tracking-tight">Crear nueva nota</h1>
            
            <form method="POST" action="{{ route('notes.store') }}">
                @csrf
                
                {{-- Campo: Título --}}
                <div class="mb-4">
                    <label class="block text-slate-700 dark:text-slate-300 font-medium mb-2">Título *</label>
                    <input type="text" name="title" value="{{ old('title') }}" 
                           class="w-full bg-transparent border-gray-300 rounded-lg focus:border-[#7700F0] focus:ring focus:ring-[#7700F0]/20 transition-all text-slate-800 dark:text-slate-100" required>
                </div>
                
                {{-- Campo: Categoría --}}
                <div class="mb-4">
                    <label class="block text-slate-700 dark:text-slate-300 font-medium mb-2">Categoría</label>
                    <select name="category_id" class="w-full bg-white border-gray-300 rounded-lg focus:border-[#7700F0] focus:ring focus:ring-[#7700F0]/20 transition-all text-slate-800 dark:text-slate-100">
                        <option value="">Sin categoría</option>
                        @foreach($categorias as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Campo: Contenido Markdown --}}
                <div class="mb-5">
                    <label class="block text-slate-700 dark:text-slate-300 font-medium mb-2">Contenido (Markdown) *</label>
                    <textarea name="content_markdown" rows="15" 
                              class="w-full bg-transparent border-gray-300 rounded-lg font-mono text-sm focus:border-[#7700F0] focus:ring focus:ring-[#7700F0]/20 transition-all text-slate-800 dark:text-slate-100" required>{{ old('content_markdown') }}</textarea>
                </div>
                
                {{-- Botones de Acción --}}
                <div class="flex gap-3 items-center">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg shadow-md shadow-emerald-600/10 transition-all active:scale-95 flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk text-sm"></i> Crear nota
                    </button>
                    <a href="{{ route('notes.index') }}" class="px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 rounded-lg font-medium transition-all text-sm active:scale-95">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>