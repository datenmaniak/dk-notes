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