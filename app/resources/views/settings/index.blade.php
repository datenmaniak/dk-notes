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
            
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf

                 <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-[#7700F0] hover:bg-purple-700 text-white rounded-lg">
                        💾 Guardar cambios
                    </button>
                </div>
                
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
                
                {{-- Ruta personal --}}
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold mb-3">📂 Configuración de directorio personal</h2>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-1">
                            Ruta personal (5-16 letras minúsculas)
                        </label>
                        <input type="text" name="ruta_personal" value="{{ $settings['ruta_personal'] }}"
                               class="w-full border-gray-300 rounded-lg focus:ring-[#7700F0] focus:border-[#7700F0]"
                               placeholder="ej: proyectos, trabajos, apuntes, diario, anotaciones, notas">
                        <p class="text-xs text-gray-500 mt-1">
                            Solo letras minúsculas, entre 5 y 16 caracteres.<br>
                        </p>
                            <p class="text-md text-red-500 mt-1">
                              Evita cambiarla después, para asegurar la sincronización.
                            </p>
                    </div>
                    
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">
                            <span class="font-medium">📁 Ruta base (app):</span><br>
                            <code class="text-xs bg-gray-200 px-1 py-0.5 rounded">{{ $settings['ruta_base'] }}</code>
                        </p>
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">📍 Tu ruta completa será:</span><br>
                            <code class="text-xs bg-gray-200 px-1 py-0.5 rounded">{{ $settings['ruta_completa'] }}</code>
                        </p>
                    </div>
                </div>

                {{-- Zona peligrosa - Solo visible para administrador --}}
                {{-- @if(Auth::user()->is_admin)
                    <div class="mt-6 p-4 border-2 border-red-700 bg-red-50 rounded-lg">
                        <h3 class="text-lg font-semibold text-red-700 mb-2">⚠️ Zona peligrosa</h3>
                        <p class="text-sm text-red-600 mb-3">
                            Esta acción eliminará <strong>TODAS las notas</strong> permanentemente.<br>
                            Las categorías, usuarios y configuraciones no se verán afectadas.
                        </p>
                        <form method="POST" action="{{ route('notes.delete-all') }}" 
                            onsubmit="return confirm('¿Eliminar TODAS las notas? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition flex items-center gap-2">
                                🗑️ Eliminar todas las notas
                            </button>
                        </form>
                    </div>
                @endif --}}
                
                {{-- Información del sistema --}}
                <div class="mb-6 pb-6 border-b border-gray-400">
                    <h2 class="text-lg font-semibold mb-3">💻 Información del sistema</h2>
                    <div class="space-y-1 text-sm text-gray-600">
                        <p>Laravel: {{ app()->version() }}</p>
                        <p>PHP: {{ phpversion() }}</p>
                        <p>Total notas: {{ \App\Models\Note::count() }}</p>
                        <p>Total categorías: {{ \App\Models\Category::count() }}</p>
                    </div>
                </div>
              
            </form>
            {{-- Zona peligrosa - Solo visible para administrador --}}
            @if(Auth::user()->is_admin)
                <div class="mt-8 p-4 border-2 border-red-300 bg-red-50 rounded-lg">
                    <h3 class="text-lg font-semibold text-red-700 mb-2">⚠️ Zona peligrosa</h3>
                    <p class="text-sm text-red-600 mb-3">
                        Esta acción eliminará <strong>TODAS las notas</strong> permanentemente.<br>
                        Las categorías, usuarios y configuraciones no se verán afectadas.
                    </p>
                    <form method="POST" action="{{ route('notes.delete-all') }}" 
                        onsubmit="return confirm('¿Eliminar TODAS las notas? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition flex items-center gap-2">
                            🗑️ Eliminar todas las notas
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>