<x-app-layout>
    <div class="p-6 pt-16 lg:pt-6 max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
                ⚙️ Configuración
            </h1>
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                
                {{-- Notas por página --}}
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold mb-3">📄 Listado de notas</h2>
                    <div class="flex items-center gap-4">
                        <span class="text-gray-700">Notas por página:</span>
                        <div class="flex gap-2">
                            @foreach([5, 10, 20] as $valor)
                                <label class="flex items-center gap-1">
                                    <input type="radio" name="notas_por_pagina" value="{{ $valor }}"
                                           {{ $settings['notas_por_pagina'] == $valor ? 'checked' : '' }}>
                                    <span>{{ $valor }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                {{-- Apariencia --}}
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold mb-3">🎨 Apariencia</h2>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-1">
                            <input type="radio" name="tema" value="light" {{ $settings['tema'] == 'light' ? 'checked' : '' }}>
                            🌞 Claro
                        </label>
                        <label class="flex items-center gap-1">
                            <input type="radio" name="tema" value="dark" {{ $settings['tema'] == 'dark' ? 'checked' : '' }}>
                            🌙 Oscuro
                        </label>
                        <label class="flex items-center gap-1">
                            <input type="radio" name="tema" value="auto" {{ $settings['tema'] == 'auto' ? 'checked' : '' }}>
                            ⚙️ Automático
                        </label>
                    </div>
                </div>
                
                {{-- Directorio de importación --}}
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold mb-3">📂 Importación</h2>
                    <div class="mb-2">
                        <label class="block text-gray-700 mb-1">Directorio de notas</label>
                        <input type="text" name="directorio_notas" value="{{ $settings['directorio_notas'] }}"
                               class="w-full border-gray-300 rounded-lg font-mono text-sm">
                        <p class="text-xs text-gray-500 mt-1">
                            Ruta donde el sistema busca archivos .md. Ej: /var/www/html/notes
                        </p>
                    </div>
                </div>
                
                {{-- Información del sistema --}}
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold mb-3">💻 Información del sistema</h2>
                    <div class="space-y-1 text-sm text-gray-600">
                        <p>Laravel: {{ app()->version() }}</p>
                        <p>PHP: {{ phpversion() }}</p>
                        <p>Total notas: {{ \App\Models\Note::count() }}</p>
                        <p>Total categorías: {{ \App\Models\Category::count() }}</p>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-[#7700F0] hover:bg-purple-700 text-white rounded-lg">
                        💾 Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>