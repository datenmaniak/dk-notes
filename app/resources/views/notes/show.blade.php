<x-app-layout>
    {{-- <x-slot name="header">
        <h1 class="text-3xl font-bold text-gray-800">{{ $note->title }}</h1>
    </x-slot> --}}
<!-- Filtro en sesión: {{ session('last_notes_filter') }} -->
<!-- Página en sesión: {{ session('last_notes_page') }} -->
    <div class="py-12">
        <div class="p-6 pt-16 lg:pt-6 max-w-4xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">


            {{-- // edicion de la nota  --}}

            {{-- Control del paginacion y categoria filtrada --}}
            @php
                $lastFilter = session('last_notes_filter');
                $lastPage = session('last_notes_page', 1);
                $backUrl = $lastFilter ? route('notes.filter', ['category' => $lastFilter, 'page' => $lastPage]) : route('notes.index', ['page' => $lastPage]);
            @endphp

                    {{-- Botón Volver --}}
                    <div class="mb-6">
                        <a href="{{ $backUrl }}"
                        class="font-bold text-green-600 hover:bg-green-200 focus:outline-2 focus:outline-offset-2 focus:outline-gray-500  px-2 py-1 inline-block">
                        ← Volver a mis notas
                    </a>
                    {{-- <a href="{{ route('notes.index', ['page' => $page ?? 1]) }}" --}}
                        {{-- class="font-bold text-green-600 hover:bg-green-200 focus:outline-2 focus:outline-offset-2 focus:outline-gray-500 px-2 py-1 inline-block"> --}}
                            {{-- ← Volver a mis notas --}}
                        {{-- </a> --}}
                    </div>

                                        
                    {{-- Título centrado como botón de edición
                    <div class="text-center mb-4">
                        <a href="{{ route('notes.edit', $note) }}" 
                           class="text-2xl font-bold text-gray-800 cursor-pointer hover:text-[#7700F0] hover:underline transition-colors inline-block">
                            {{ $note->title }}
                        </a>
                    </div> --}}

                     {{-- Título alineado a la izquierda, como botón de edición --}}
                    <div class="mb-4">
                        <a href="{{ route('notes.edit', $note) }}" 
                            class="text-lg mb-2 ml-auto px-3 py-1.5 text-sm text-[#7700F0] border border-[#7700F0] bg-transparent rounded-md hover:bg-[#7700F0] hover:text-white transition-colors duration-200">
                           {{-- class="text-lg font-bold cursor-pointer  hover:bg-purple-600 transition-colors inline-block hover:text-white px-2 py-1 -ml-2"> --}}
                            {{ $note->title }}
                        </a>
                    </div>

                    {{-- Prueba --}}
                      {{-- <div class="flex justify-end"> --}}
                        {{-- <button
                            type="button"
                            onclick="openAssignTagsModal()"
                            class="mb-2 ml-auto px-3 py-1.5 text-sm text-[#7700F0] border border-[#7700F0] bg-transparent rounded-md hover:bg-[#7700F0] hover:text-white transition-colors duration-200">
                            + Asignar etiqueta
                        </button>
                    </div> --}}
                    
                    {{-- Línea separadora --}}
                    <div class="border-b border-gray-200 mb-6"></div>
                    
                    
                    {{-- Metadatos --}}
                    <div class="flex flex-wrap gap-3 text-sm text-gray-500 mb-4 pb-4 border-b border-gray-200">
                        {{-- Categoría --}}
                        <div class="flex items-center gap-1">
                            <span>📂</span>
                            <span>Categoría:</span>
                            <span class="text-gray-700">{{ $note->category?->name ?? 'Sin categoría' }}</span>
                        </div>
                        
                        <span class="text-gray-300">|</span>
                        
                        {{-- Fecha de creación --}}
                        <div class="flex items-center gap-1">
                            <span>📅</span>
                            <span>Creada:</span>
                            <span class="text-gray-700">{{ $note->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        
                        {{-- Fecha de actualización (si es diferente) --}}
                        @if($note->created_at != $note->updated_at)
                            <span class="text-gray-300">|</span>
                            <div class="flex items-center gap-1">
                                <span>✏️</span>
                                <span>Actualizada:</span>
                                <span class="text-gray-700">{{ $note->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                        
                        <span class="text-gray-300">|</span>
                        
                        {{-- Etiquetas --}}
                        <div class="flex items-center gap-1 flex-wrap">
                            <span>🏷️</span>
                            <span>Etiquetas:</span>
                            @if($note->tags->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($note->tags as $tag)
                                        <span class="bg-gray-200 text-gray-800 px-2 py-0.5 rounded-full text-xs">
                                        {{-- <span class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full text-xs"> --}}
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400">Sin etiquetas</span>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Contenido de la nota --}}
                    <div class="prose max-w-none mt-6">
                        {!! $note->content_html !!}
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>