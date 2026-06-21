<x-app-layout>
    <div class="p-6 pt-16 lg:pt-6 max-w-3xl mx-auto">
        <div class="card-daten shadow-xl p-6">
            <h1 class="text-2xl font-bold mb-6 flex items-center gap-3 text-daten-primary">
                <i class="fa-solid fa-gear text-brand-glow text-3xl"></i>
                Configuración
            </h1>
            
            @if(session('success'))
                <div class="bg-green-100 dark:bg-green-900/20 border border-green-400 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded mb-4 flex items-center">
                    <i class="fa-solid fa-check-circle mr-2"></i> {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-100 dark:bg-red-900/20 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded mb-4 flex items-center">
                    <i class="fa-solid fa-exclamation-circle mr-2"></i> {{ session('error') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf

                <div class="flex justify-end mb-6">
                    <button type="submit" class="px-5 py-2.5 bg-brand-glow hover:bg-brand-accent text-white font-medium rounded-lg transition-all duration-200 hover:shadow-lg hover:shadow-brand-glow/30 active:scale-[0.98] flex items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        Guardar cambios
                    </button>
                </div>
                
                {{-- Notas por página --}}
                <div class="mb-6 pb-6 border-b border-daten">
                    <h2 class="text-lg font-semibold text-daten-primary mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-list text-daten-muted"></i>
                        Listado de notas
                    </h2>
                    <div class="flex items-center gap-4 flex-wrap">
                        <span class="text-daten-secondary">Notas por página:</span>
                        <div class="flex gap-4">
                            @foreach([5, 10, 20] as $valor)
                                <label class="flex items-center gap-1.5 text-daten-secondary cursor-pointer hover:text-daten-primary transition-colors">
                                    <input type="radio" name="notas_por_pagina" value="{{ $valor }}"
                                           {{ $settings['notas_por_pagina'] == $valor ? 'checked' : '' }}
                                           class="text-brand-glow focus:ring-brand-glow/30 border-daten">
                                    <span>{{ $valor }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                {{-- Apariencia --}}
                <div class="mb-6 pb-6 border-b border-daten">
                    <h2 class="text-lg font-semibold text-daten-primary mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-palette text-daten-muted"></i>
                        Apariencia
                    </h2>
                    <div class="flex gap-6 flex-wrap">
                        <label class="flex items-center gap-1.5 text-daten-secondary cursor-pointer hover:text-daten-primary transition-colors">
                            <input type="radio" name="tema" value="light" {{ $settings['tema'] == 'light' ? 'checked' : '' }}
                                   class="text-brand-glow focus:ring-brand-glow/30 border-daten">
                            <i class="fa-solid fa-sun"></i> Claro
                        </label>
                        <label class="flex items-center gap-1.5 text-daten-secondary cursor-pointer hover:text-daten-primary transition-colors">
                            <input type="radio" name="tema" value="dark" {{ $settings['tema'] == 'dark' ? 'checked' : '' }}
                                   class="text-brand-glow focus:ring-brand-glow/30 border-daten">
                            <i class="fa-solid fa-moon"></i> Oscuro
                        </label>
                        <label class="flex items-center gap-1.5 text-daten-secondary cursor-pointer hover:text-daten-primary transition-colors">
                            <input type="radio" name="tema" value="auto" {{ $settings['tema'] == 'auto' ? 'checked' : '' }}
                                   class="text-brand-glow focus:ring-brand-glow/30 border-daten">
                            <i class="fa-solid fa-display"></i> Automático
                        </label>
                    </div>
                </div>
                
                {{-- Ruta personal --}}
                <div class="mb-6 pb-6 border-b border-daten">
                    <h2 class="text-lg font-semibold text-daten-primary mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-folder text-daten-muted"></i>
                        Configuración de directorio personal
                    </h2>
                    
                    <div class="mb-4">
                        <label class="block text-daten-secondary font-medium mb-1.5">
                            Ruta personal (5-16 letras minúsculas)
                        </label>
                        <input type="text" name="ruta_personal" value="{{ $settings['ruta_personal'] }}"
                               class="w-full input-daten placeholder:text-daten-muted focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30 transition-all duration-200"
                               placeholder="ej: proyectos, trabajos, apuntes, diario, anotaciones, notas">
                        <p class="text-xs text-daten-muted mt-1.5">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            Solo letras minúsculas, entre 5 y 16 caracteres.
                        </p>
                        <p class="text-sm text-red-500 mt-1 flex items-center gap-1.5">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Evita cambiarla después, para asegurar la sincronización.
                        </p>
                    </div>
<!--                     
                    <div class="bg-daten-input p-4 rounded-lg border border-daten">
                        <p class="text-sm text-daten-secondary mb-1.5">
                            <i class="fa-solid fa-folder-open text-daten-muted mr-1.5"></i>
                            <span class="font-medium">Ruta base (app):</span><br>
                            <code class="text-xs bg-daten-card px-1.5 py-0.5 rounded border border-daten text-daten-secondary">{{ $settings['ruta_base'] }}</code>
                        </p>
                        <p class="text-sm text-daten-secondary">
                            <i class="fa-solid fa-location-dot text-daten-muted mr-1.5"></i>
                            <span class="font-medium">Tu ruta completa será:</span><br>
                            <code class="text-xs bg-daten-card px-1.5 py-0.5 rounded border border-daten text-daten-secondary">{{ $settings['ruta_completa'] }}</code>
                        </p>
                    </div> -->
                
                    {{-- Ruta personal --}}
<div class="mb-6 pb-6 border-b border-daten">
    <h2 class="text-lg font-semibold text-daten-primary mb-3 flex items-center gap-2">
        <i class="fa-solid fa-folder text-daten-muted"></i>
        Configuración de directorio personal
    </h2>
    
    <div class="mb-4">
        <label class="block text-daten-secondary font-medium mb-1.5">
            Ruta personal (5-16 letras minúsculas)
        </label>
        <input type="text" name="ruta_personal" value="{{ $settings['ruta_personal'] }}"
               class="w-full input-daten placeholder:text-daten-muted focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30 transition-all duration-200"
               placeholder="ej: proyectos, trabajos, apuntes, diario, anotaciones, notas">
        <p class="text-xs text-daten-muted mt-1.5">
            <i class="fa-solid fa-info-circle mr-1"></i>
            Solo letras minúsculas, entre 5 y 16 caracteres.
        </p>
        <p class="text-sm text-red-500 mt-1 flex items-center gap-1.5">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Evita cambiarla después, para asegurar la sincronización.
        </p>
    </div>
    
    {{-- ÁREA INFORMATIVA: Rutas del sistema --}}
            <div class="w-[90%] mx-auto bg-daten-input p-3 sm:p-4 rounded-lg border border-daten space-y-2">
                <p class="text-sm text-daten-secondary flex flex-col sm:flex-row sm:items-center gap-1">
                    <span class="flex items-center gap-1.5 font-medium whitespace-nowrap">
                        <i class="fa-solid fa-folder-open text-daten-muted"></i>
                        Ruta base (app):
                    </span>
                    <code class="text-[10px] sm:text-xs md:text-sm bg-daten-card px-2 py-0.5 rounded border border-daten text-daten-secondary break-all">
                        {{ $settings['ruta_base'] }}
                    </code>
                </p>
                <p class="text-sm text-daten-secondary flex flex-col sm:flex-row sm:items-center gap-1">
                    <span class="flex items-center gap-1.5 font-medium whitespace-nowrap">
                        <i class="fa-solid fa-location-dot text-daten-muted"></i>
                        Tu ruta completa será:
                    </span>
                    <code class="text-[10px] sm:text-xs md:text-sm bg-daten-card px-2 py-0.5 rounded border border-daten text-daten-secondary break-all">
                        {{ $settings['ruta_completa'] }}
                    </code>
                </p>
            </div>
        </div>
                    

                {{-- Información del sistema --}}
                <div class="mb-6 pb-6 border-b border-daten">
                    <h2 class="text-lg font-semibold text-daten-primary mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-computer text-daten-muted"></i>
                        Información del sistema
                    </h2>
                    <div class="space-y-1 text-sm text-daten-secondary">
                        <p><span class="font-medium">Laravel:</span> {{ app()->version() }}</p>
                        <p><span class="font-medium">PHP:</span> {{ phpversion() }}</p>
                        <p><span class="font-medium">Total notas:</span> {{ \App\Models\Note::count() }}</p>
                        <p><span class="font-medium">Total categorías:</span> {{ \App\Models\Category::count() }}</p>
                    </div>
                </div>
              
                {{-- Botón Guardar (inferior) --}}
                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-brand-glow hover:bg-brand-accent text-white font-medium rounded-lg transition-all duration-200 hover:shadow-lg hover:shadow-brand-glow/30 active:scale-[0.98] flex items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        Guardar cambios
                    </button>
                </div>
            </form>

            {{-- Zona peligrosa - Solo visible para administrador --}}
            @if(Auth::user()->is_admin)
                <div class="mt-8 p-5 border-2 border-red-500/40 dark:border-red-500/30 bg-red-50 dark:bg-red-950/20 rounded-xl">
                    <h3 class="text-lg font-semibold text-red-700 dark:text-red-400 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Zona peligrosa
                    </h3>
                    <p class="text-sm text-red-600 dark:text-red-300 mb-4">
                        Esta acción eliminará <strong>TODAS las notas</strong> permanentemente.<br>
                        Las categorías, usuarios y configuraciones no se verán afectadas.
                    </p>
                    <form method="POST" action="{{ route('notes.delete-all') }}" 
                        onsubmit="return confirm('¿Eliminar TODAS las notas? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-all duration-200 flex items-center gap-2 active:scale-[0.98]">
                            <i class="fa-solid fa-trash-can"></i>
                            Eliminar todas las notas
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>