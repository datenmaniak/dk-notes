<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- // para resaltado de sintaxis  --}}
    <!-- Para vs (claro con contraste) -->

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/vs2015.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        hljs.highlightAll();
    });
</script>
    {{-- //  Es resaltado de sintaxis (Syntax Highlighting) para bloques --}}
    {{-- //  de código en Markdown. --}}


    <title>{{ config('app.name', 'DKNotes') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">

   
    {{-- Contenedor padre --}}
    {{-- <div class="min-h-screen bg-gray-100 flex flex-col lg:flex-row"> // before altura automatica (100vh) --}}
        <div class="h-screen bg-gray-100 flex flex-col lg:flex-row overflow-hidden">
        {{-- Botón hamburguesa para móvil --}}
        <button id="menuToggle" class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-lg bg-[#7700F0] text-white shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        {{-- Overlay para móvil --}}
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

        {{-- Sidebar --}}
        <aside id="sidebar" class="fixed lg:relative inset-y-0 left-0 w-80 lg:w-80 bg-[#7700F0] text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-50 flex-shrink-0 h-screen overflow-y-auto lg:block">
            <div class="h-full flex flex-col">
                {{-- User Info --}}
                <div class="p-6 border-b border-purple-400">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-purple-300 flex items-center justify-center text-purple-800 font-bold">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold">{{ Auth::user()->name }}</p>
                            <p class="text-sm text-purple-200 truncate">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 p-4 space-y-2">
                    <a href="{{ route('notes.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-purple-700 transition">
                        <span>📋</span>
                        <span>Mis Notas</span>
                    </a>
                </nav>

                {{-- remove  --}}
                {{-- Categorías con contador --}}
                {{-- <div class="px-4 pb-4">
                    <h3 class="text-xs font-semibold text-purple-200 uppercase tracking-wider mb-2">Categorías</h3>
                    <div class="space-y-1">
                        @php
                            use App\Models\Category;
                            $categoriasDirectas = Category::withCount('notes')->get();
                        @endphp
                        @forelse($categoriasDirectas as $cat)
                            <a href="{{ route('notes.filter', $cat->slug) }}" 
                            class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-purple-700 transition text-sm">
                                <div class="flex items-center space-x-2">
                                    <span>📁</span>
                                    <span>{{ $cat->name }}</span>
                                </div>
                                <span class="text-xs bg-purple-700 px-2 py-0.5 rounded-full">{{ $cat->notes_count }}</span>
                            </a>
                        @empty
                            <div class="text-sm text-purple-200 px-3 py-2">
                                No hay categorías
                            </div>
                        @endforelse
                    </div>
                </div> --}}
                {{-- remove hasta aqui --}}

                {{-- BEGIN selector de categorias tipo selector  --}}
                {{-- Selector de categorías --}}
                <div class="px-4 pb-4">
                    <h3 class="text-xs font-semibold text-purple-200 uppercase tracking-wider mb-2">Filtrar por categoría</h3>
                    <select id="categorySelect" class="w-full px-3 py-2 rounded-lg bg-purple-700 text-gray-900 border-none focus:ring-2 focus:ring-purple-300">
                        <option value="{{ route('notes.index') }}"  class="text-gray-900" >📋 Todas las notas</option>
                        @php
                            use App\Models\Category;
                            $categoriasSelect = Category::withCount('notes')->get();
                        @endphp
                        @foreach($categoriasSelect as $cat)
                            <option value="{{ route('notes.filter', $cat->slug) }}" 
                                class="text-gray-900"
                                {{ request()->route('categorySlug') == $cat->slug ? 'selected' : '' }}>
                                📁 {{ $cat->name }} ({{ $cat->notes_count }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <script>
                    document.getElementById('categorySelect')?.addEventListener('change', function() {
                        window.location.href = this.value;
                    });
                </script>

                {{-- Administración (solo para admin) --}}
                @if(Auth::check() && Auth::user()->is_admin)
                    <div class="px-4 pb-4 border-t border-purple-400 mt-2 pt-4">
                        <h3 class="text-xs font-semibold text-purple-200 uppercase tracking-wider mb-2">Administración</h3>
                        <div class="space-y-1">
                            <form method="POST" action="{{ route('notes.take-ownership') }}" 
                                onsubmit="return confirm('¿Reasignar todas las notas a tu usuario? Esta acción no se puede deshacer.')">
                                @csrf
                                <button type="submit" class="w-full flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-purple-700 transition text-sm">
                                    <span>🏠</span>
                                    <span>Tomar posesión de notas</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Logout --}}
                <div class="p-4 border-t border-purple-400">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-purple-700 transition w-full">
                            <span>🚪</span>
                            <span>Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main Content --}}
        {{-- <main class="lg:ml-64 min-h-screen"> // before apply (100vh) --}}
            <main class="flex-1 lg:ml-64 overflow-y-auto">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded m-4">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded m-4">
                    {{ session('error') }}
                </div>
            @endif
            
            @if(session('warning'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded m-4">
                    {{ session('warning') }}
                </div>
            @endif
            
            {{ $slot }}
        </main>
    </div>

    <!-- Highlight.js JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
        hljs.highlightAll();
        });
    </script>

    <script>
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        const overlay = document.getElementById('sidebarOverlay');

        // // Resaltado de sintasis
        // document.addEventListener('DOMContentLoaded', () => {
        //     hljs.highlightAll();
        // });
        
        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        if (menuToggle) {
            menuToggle.addEventListener('click', openSidebar);
        }
        
        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }
        
        // Cerrar al hacer clic en un enlace (móvil)
        sidebar.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    closeSidebar();
                }
            });
        
        });
    </script>
    
    
</body>
</html>