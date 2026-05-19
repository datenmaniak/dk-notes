<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $note->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        {{-- <div class="max-w-4xl mx-auto sm:px-6 lg:px-8"> --}}
        <div class="p-6 pt-16 lg:pt-6 max-w-4xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('notes.index') }}" class="text-blue-600 hover:underline">← Volver a mis notas</a>
                    </div>
                    
                    <div class="text-sm text-gray-500 mb-4">
                        <span>Categoría: {{ $note->category?->name ?? 'Sin categoría' }}</span>
                        <span class="mx-2">•</span>
                        <span>Creada: {{ $note->created_at->format('d/m/Y') }}</span>
                    </div>
                    
                    <div class="prose max-w-none mt-6">
                        {!! $note->content_html !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>