<x-app-layout>
    <div class="p-6 pt-16 lg:pt-6 max-w-4xl mx-auto">
        <div class="card-daten shadow-xl p-6">
            
            {{-- Enlace de retorno --}}
            <div class="mb-6 pb-4">
                <a href="{{ route('notes.index', ['page' => request()->input('page', 1)]) }}"
                   class="text-sm font-medium text-daten-secondary border border-daten bg-daten-card hover:bg-brand-glow/10 hover:text-brand-glow px-3 py-2 inline-flex items-center gap-2 transition-all duration-200 rounded-lg group">
                   <i class="fa-solid fa-arrow-left text-xs"></i> Volver a mis notas
                </a>
            </div>
            
            <h1 class="text-2xl font-bold mb-6 tracking-tight flex items-center gap-2 text-daten-primary">
                <i class="fa-solid fa-file-circle-plus text-brand-glow/80"></i> Crear nueva nota
            </h1>
            
            <form method="POST" action="{{ route('notes.store') }}">
                @csrf
                
                {{-- Campo: Título --}}
                <div class="mb-4">
                    <label class=" text-daten-secondary font-medium mb-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-heading text-xs text-daten-muted"></i> Título *
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" 
                           class="w-full rounded-lg input-daten placeholder:text-daten-muted focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30 transition-all duration-200" required>
                </div>
                
                {{-- Campo: Categoría --}}
                <div class="mb-4">
                    <label class=" text-daten-secondary font-medium mb-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-folder text-xs text-daten-muted"></i> Categoría
                    </label>
                    <select name="category_id" class="w-full rounded-lg input-daten focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30 transition-all duration-200">
                        <!-- <option value="">Sin categoría</option> -->
                        <option value="">-- Ninguna, usar "General" --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Campo: Contenido Markdown --}}
                <div class="mb-5">
                    <label class=" text-daten-secondary font-medium mb-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-code text-xs text-daten-muted"></i> Contenido (Markdown) *
                    </label>
                    <textarea name="content_markdown" rows="7" 
                              class="w-full input-daten rounded-lg font-mono text-sm placeholder:text-daten-muted focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30 transition-all duration-200" required>{{ old('content_markdown') }}</textarea>
                </div>
                
                {{-- Botones de Acción --}}
                <div class="flex gap-3 items-center flex-wrap">
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg shadow-md shadow-emerald-600/10 transition-all duration-200 active:scale-[0.98] flex items-center gap-2">
                        <i class="fa-solid fa-circle-check"></i> Crear nota
                    </button>
                    <a href="{{ route('notes.index') }}" 
                       class="px-4 py-2.5 border border-daten text-daten-secondary bg-daten-card hover:bg-brand-glow/10 hover:text-brand-glow rounded-lg font-medium transition-all duration-200 text-sm active:scale-[0.98] flex items-center gap-1.5">
                        <i class="fa-solid fa-ban text-xs opacity-70"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>