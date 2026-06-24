<div class="text-3xl text-daten-muted mb-2">
                            <i class="fa-solid fa-file-lines text-brand-muted"></i>
                        </div>




                         {{-- Estilos para el contenido de las notas --}}
    <style>
        .note-content {
            white-space: pre-line;
        }
        /* Mejoras para el contenido de notas en modo oscuro */
        .dark .prose {
            color: #e2e8f0;
        }
        .dark .prose h1,
        .dark .prose h2,
        .dark .prose h3,
        .dark .prose h4 {
            color: #f1f5f9;
        }
        .dark .prose a {
            color: #a366ff;
        }
        .dark .prose a:hover {
            color: #7d2eff;
        }
        .dark .prose blockquote {
            border-left-color: #7700F0;
            color: #94a3b8;
        }
        .dark .prose code {
            background-color: #1a1a2e;
            color: #e2e8f0;
        }
        .dark .prose pre {
            background-color: #1a1a2e;
        }
    </style>


    ## Quiero agregar estas gamas de colores a mi paleta

    En base al primario #7700F0

    ### Colores Cuadrados:
    - Verde: #00F0D7
    - Amarillo: #EDF000
    - Naranja: #F04C00
    - lila claro: #67349B

    ### Compuestos:
    - Verde: #46F000
    - Amarillo: #EDF000
    - Azul: #000BF0
    

            <!-- Se aplica un estilo semantico a la etiquetas  -->
                                <!-- @if($note->tags->count() > 0)
                                        <div class="flex flex-wrap gap-1.5 pt-1">
                                            @foreach($note->tags as $tag)
                                                @php
                                                    $tagName = $tag->name;
                                                    $color = $tagColorMap[$tagName] ?? $defaultTagColor;
                                                @endphp
                                                <span class="inline-flex items-center gap-1 {{ $color['bg'] }} {{ $color['text'] }} px-2 py-0.5 rounded text-[11px] font-medium border {{ $color['border'] }}">
                                                    <i class="fa-solid fa-tag text-[9px] opacity-60"></i>
                                                    {{ $tagName }}
                                                </span>
                                            @endforeach
                                        </div>
                                @endif
                                 -->



{{-- ============================================ --}}
{{-- TARJETAS DE ESTADÍSTICAS --}}
{{-- ============================================ --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    {{-- Tarjeta: Total Notas --}}
    <div class="bg-daten-card rounded-xl border border-daten shadow-sm p-6 transition-all duration-300 hover:shadow-md">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-daten-secondary uppercase tracking-wider">Total Notas</p>
                <p class="text-3xl font-bold text-daten-primary tracking-tight">{{ $totalNotas }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-brand-glow/10 flex items-center justify-center text-brand-glow">
                <i class="fa-solid fa-note-sticky text-xl"></i>
            </div>
        </div>
    </div>

    {{-- Tarjeta: Total Categorías --}}
    <div class="bg-daten-card rounded-xl border border-daten shadow-sm p-6 transition-all duration-300 hover:shadow-md">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-daten-secondary uppercase tracking-wider">Total Categorías</p>
                <p class="text-3xl font-bold text-daten-primary tracking-tight">{{ $totalCategorias }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-brand-glow/10 flex items-center justify-center text-brand-glow">
                <i class="fa-solid fa-folder text-xl"></i>
            </div>
        </div>
    </div>
