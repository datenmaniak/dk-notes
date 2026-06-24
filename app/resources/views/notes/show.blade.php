<x-app-layout>
    {{-- <x-slot name="header">
        <h1 class="text-3xl font-bold text-gray-800">{{ $note->title }}</h1>
    </x-slot> --}}
<div class="py-12">
        <div class="p-6 pt-16 lg:pt-6 max-w-4xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

            {{-- // edicion de la nota  --}}
            {{-- Control de la paginación y categoría filtrada --}}
            @php
                $lastFilter = session('last_notes_filter');
                $lastPage = session('last_notes_page', 1);
                $backUrl = $lastFilter ? route('notes.filter', ['category' => $lastFilter, 'page' => $lastPage]) : route('notes.index', ['page' => $lastPage]);
            @endphp

                    {{-- Botón Volver (Estilizado, más grande y con icono de salida) --}}
                    <div id="return-here"  class="mb-8 border-b border-gray-100 pb-3">
                        <a href="{{ $backUrl }}"
                           class="text-base md:text-lg font-medium text-gray-600 hover:text-[#7700F0] hover:bg-purple-50 px-3 py-2 inline-flex items-center gap-2.5 transition-all rounded-lg group">
                            <i class="fa-solid fa-arrow-left-from-line text-gray-400 group-hover:text-[#7700F0] transition-colors"></i> 
                            Volver a la lista de  notas
                        </a>
                    </div>

                    {{-- Título Centrado con Icono de Propósito y Efecto de Edición --}}
                    <div class="text-center mb-8 flex flex-col items-center justify-center">
                        <div class="text-3xl text-gray-300 mb-2">
                            <i class="fa-solid fa-file-lines text-purple-300"></i>
                        </div>
                        
                        <a href="{{ route('notes.edit', $note) }}" 
                           class="text-2xl md:text-3xl font-extrabold text-[#7700F0] hover:text-[#5b00b8] transition-colors duration-200 inline-flex items-center gap-3 group px-4 py-1 rounded-xl hover:bg-purple-50/50">
                            {{ $note->title }}
                            <i class="fa-solid fa-pen text-sm opacity-0 group-hover:opacity-60 transition-opacity text-gray-400" title="Editar nota"></i>
                        </a>
                    </div>
           
                    
                    {{-- Línea separadora --}}
                    <div class="border-b border-gray-200 mb-6"></div>
                    
                    
                    {{-- Metadatos --}}
                    <div class="flex flex-wrap gap-3 text-sm text-gray-500 mb-4 pb-4 border-b border-gray-200">
                        {{-- Categoría --}}
                        <div class="flex items-center gap-1">
                            <i class="fa-solid fa-folder text-gray-400"></i>
                            <span>Categoría:</span>
                            <span class="text-gray-700 font-medium">{{ $note->category?->name ?? 'Sin categoría' }}</span>
                        </div>
                        
                        <span class="text-gray-300">|</span>
                        
                        {{-- Fecha de creación --}}
                        <div class="flex items-center gap-1">
                            <i class="fa-solid fa-calendar-days text-gray-400"></i>
                            <span>Creada:</span>
                            <span class="text-gray-700 font-medium">{{ $note->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        
                        {{-- Fecha de actualización (si es diferente) --}}
                        @if($note->created_at != $note->updated_at)
                            <span class="text-gray-300">|</span>
                            <div class="flex items-center gap-1">
                                <i class="fa-solid fa-pencil text-gray-400"></i>
                                <span>Actualizada:</span>
                                <span class="text-gray-700 font-medium">{{ $note->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                        
                        <span class="text-gray-300">|</span>
                        
                        {{-- Etiquetas --}}
                        <!-- <div class="flex items-center gap-1 flex-wrap">
                            <i class="fa-solid fa-tag text-gray-400"></i>
                            <span>Etiquetas:</span>
                            @if($note->tags->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($note->tags as $tag)
                                        <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs border border-gray-200">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 italic">Sin etiquetas</span>
                            @endif
                        </div> -->
                        @if($note->tags->count() > 0)
                            <div class="flex flex-wrap gap-1">
                            @foreach($note->tags as $tag)
                                @php
                                    $color = \App\Helpers\TagColors::getForTag($tag->name);
                                @endphp
                                <span class="inline-flex items-center gap-1 {{ $color['bg'] }} {{ $color['text'] }} px-2 py-0.5 rounded text-xs font-medium border {{ $color['border'] }}">
                                    <i class="fa-solid fa-tag text-[8px] opacity-60"></i>
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                            </div>
                        @else
                            @php
                                $color = \App\Helpers\TagColors::getForTag('nolabels');
                            @endphp
                            <span class="inline-flex items-center gap-1 {{ $color['bg'] }} {{ $color['text'] }} px-2 py-0.5 rounded text-xs font-medium border {{ $color['border'] }}">
                                <i class="fa-solid fa-tag text-[8px] opacity-60"></i>
                                Sin etiquetas
                            </span>
                        @endif

                    </div>
                    
                    {{-- Contenido de la nota --}}
                    <div class="prose max-w-none mt-6">
                        {!! $note->content_html !!}
                    </div>

                
                    <hr class="border-t-2 border-daten mt-6 my-6">

                    <div class="mb-8 pb-4 mt-10 ">
                    <a href="#return-here"
                    class="text-base md:text-lg font-medium text-daten-secondary hover:text-brand-glow hover:bg-brand-glow/5 px-3 py-2 inline-flex items-center gap-2.5 transition-all rounded-lg group">
                        <i class="fa-solid fa-circle-up text-2xl md:text-3xl text-daten-muted group-hover:text-brand-glow transition-colors"></i> 
                        Ir arriba del documento
                    </a>
                </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Agrega estilo para apreciar el espaciado en el contenido de las notas  --}}
    <style>
        .note-content {
            white-space: pre-line;
        }
    </style>
</x-app-layout>