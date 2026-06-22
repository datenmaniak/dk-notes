<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      @php
          use App\Models\UserSetting;
          $tema = UserSetting::getValue(Auth::id(), 'tema', 'auto');
          $darkClass = '';
          if ($tema == 'dark') {
              $darkClass = 'dark';
          } elseif ($tema == 'auto' && isset($_COOKIE['theme_preference'])) {
              $darkClass = $_COOKIE['theme_preference'] === 'dark' ? 'dark' : '';
          }
      @endphp
      class="{{ $darkClass }}">


<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/798dc59432.js" crossorigin="anonymous"></script>

    <!-- Highlight.js para resaltado de sintaxis -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/styles/default.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/highlight.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            hljs.highlightAll();
        });
    </script>

    <title>{{ config('app.name', 'dk-notes') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts: Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

      
</head>

<body class="font-sans antialiased page-daten text-daten-primary">

    {{-- Contenedor padre --}}
    <div class="h-screen page-daten flex flex-col lg:flex-row overflow-hidden">
        
        {{-- Botón hamburguesa para móvil --}}
        <button id="menuToggle" class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-lg bg-brand-glow text-white shadow-lg hover:bg-brand-accent transition-colors">
            <i class="fas fa-bars"></i>
        </button>

        {{-- Overlay para móvil --}}
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

        {{-- Sidebar --}}
        <aside id="sidebar" class="fixed lg:relative inset-y-0 left-0 w-56 lg:w-56 bg-brand-glow text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-50 flex-shrink-0 h-full overflow-y-auto lg:block">
            <div class="h-full flex flex-col">
                {{-- User Info --}}
                <div class="p-6 border-b border-brand-glow/40">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-brand-muted/30 flex items-center justify-center text-white font-bold">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold">{{ Auth::user()->name }}</p>
                            <p class="text-sm text-brand-muted truncate">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 p-4 space-y-2">
                    <a href="{{ route('notes.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-brand-accent transition-colors">
                        <i class="fas fa-sticky-note"></i>
                        <span>Mis Notas</span>
                    </a>
                    <a href="#" onclick="openTagsModal(); return false;" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-brand-accent transition-colors">
                        <i class="fas fa-tags"></i>
                        <span># Etiquetas</span>
                    </a>
                    <a href="{{ route('settings.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-brand-accent transition-colors">
                        <i class="fas fa-cog"></i>
                        <span>Configuración</span>
                    </a>
                </nav>

                {{-- Selector de categorías --}}
                <div class="px-4 pb-4 max-h-48 overflow-y-auto">
                    <h3 class="text-xs font-semibold text-brand-muted uppercase tracking-wider mb-2">
                        <i class="fas fa-filter"></i> Filtrar por categoría
                    </h3>
                    <select id="categorySelect" class="w-48 mx-auto px-2 py-1 rounded-lg bg-white text-gray-900 border border-gray-300 text-xs truncate">
                        <option value="{{ route('notes.index') }}" class="text-gray-900">
                            <i class="fas fa-list"></i> Todas las notas
                        </option>
                        @php
                            use App\Models\Category;
                            $categoriasSelect = Category::withCount('notes')->get();
                        @endphp
                        @foreach($categoriasSelect as $cat)
                            <option value="{{ route('notes.filter', $cat->slug) }}" 
                                class="text-gray-900"
                                {{ request()->route('categorySlug') == $cat->slug ? 'selected' : '' }}>
                                <i class="fas fa-folder"></i> {{ Str::limit($cat->name, 24) }} ({{ $cat->notes_count }})
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
                    <div class="px-4 pb-4 border-t border-brand-glow/40 mt-2 pt-4">
                        <h3 class="text-xs font-semibold text-brand-muted uppercase tracking-wider mb-2">
                            <i class="fas fa-shield-alt"></i> Administración
                        </h3>
                        <div class="space-y-1">
                            <form method="POST" action="{{ route('notes.take-ownership') }}" 
                                onsubmit="return confirm('¿Reasignar todas las notas a tu usuario? Esta acción no se puede deshacer.')">
                                @csrf
                                <button type="submit" class="w-full flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-brand-accent transition-colors text-sm">
                                    <i class="fas fa-home"></i>
                                    <span>Tomar posesión de notas</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Logout --}}
                <div class="p-4 border-t border-brand-glow/40">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-brand-accent transition-colors w-full">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 overflow-y-auto page-daten">
            @if(session('success'))
                <div class="bg-green-100 dark:bg-green-900/20 border border-green-400 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded m-4 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-100 dark:bg-red-900/20 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded m-4 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                </div>
            @endif
            
            @if(session('warning'))
                <div class="bg-yellow-100 dark:bg-yellow-900/20 border border-yellow-400 dark:border-yellow-800 text-yellow-700 dark:text-yellow-400 px-4 py-3 rounded m-4 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('warning') }}
                </div>
            @endif
            
            {{ $slot }}
        </main>
    </div>

    {{-- Modal de gestión de etiquetas --}}
    <div id="tagsModal" class="fixed inset-0 bg-black/50 z-50 hidden">
        <div class="flex items-center justify-center min-h-full w-full">
            <div class="bg-daten-card rounded-lg shadow-xl max-w-md w-full mx-4 flex flex-col max-h-[90vh] border border-daten transition-colors duration-300">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-daten-primary mb-4">
                        <i class="fas fa-tags"></i> Editar etiquetas
                    </h3>
                
                <div class="mb-4">
                    <div class="flex gap-2">
                        <input type="text" id="newTagName" placeholder="Nueva etiqueta" 
                            class="flex-1 px-4 py-2 rounded-lg input-daten placeholder:text-daten-muted focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30 transition-colors">
                        <button id="createTagBtn" class="px-4 py-2 bg-brand-glow text-white rounded-lg hover:bg-brand-accent transition-colors">
                            <i class="fas fa-plus"></i> Crear
                        </button>
                    </div>
                </div>
                
                <div id="tagsList" class="space-y-2 max-h-64 overflow-y-auto">
                    <!-- Las etiquetas se cargarán aquí -->
                </div>
                
                <div class="mt-4 text-right">
                    <button onclick="closeTagsModal()" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
                        <i class="fas fa-times"></i> Salir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentTags = [];
        let isAdmin = {{ Auth::user()->is_admin ? 'true' : 'false' }};
        
        function openTagsModal() {
            document.getElementById('tagsModal').classList.remove('hidden');
            loadTags();
        }
        
        function closeTagsModal() {
            document.getElementById('tagsModal').classList.add('hidden');
        }
        
        function loadTags() {
            fetch('{{ route("tags.index") }}')
                .then(response => response.json())
                .then(tags => {
                    currentTags = tags;
                    const container = document.getElementById('tagsList');
                    container.innerHTML = '';
                    tags.forEach(tag => {
                        const div = document.createElement('div');
                        div.className = 'flex items-center justify-between p-2 bg-daten-input rounded-lg transition-colors';
                        div.innerHTML = `
                            <span class="text-daten-primary"><i class="fas fa-tag"></i> ${tag.name}</span>
                            ${isAdmin ? `<button onclick="deleteTag(${tag.id})" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>` : ''}
                        `;
                        container.appendChild(div);
                    });
                });
        }
        
        function deleteTag(tagId) {
            if (!confirm('¿Eliminar esta etiqueta?')) return;
            
            fetch(`/tags/${tagId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            }).then(() => {
                loadTags();
            });
        }
        
        document.getElementById('createTagBtn')?.addEventListener('click', function() {
            const name = document.getElementById('newTagName').value.trim();
            if (!name) return;
            
            fetch('{{ route("tags.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name: name })
            }).then(response => response.json())
            .then(() => {
                document.getElementById('newTagName').value = '';
                loadTags();
            });
        });

        // Sidebar toggle para móvil
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        const overlay = document.getElementById('sidebarOverlay');
        
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

        // ============================================
        // TOGGLE DE MODO OSCURO/CLARO
        // ============================================
        function toggleDarkMode() {
            const htmlElement = document.documentElement;
            const isDark = htmlElement.classList.contains('dark');
            
            if (isDark) {
                htmlElement.classList.remove('dark');
                document.cookie = 'theme_preference=light; path=/; max-age=31536000';
            } else {
                htmlElement.classList.add('dark');
                document.cookie = 'theme_preference=dark; path=/; max-age=31536000';
            }
        }
    </script>
    
</body>
</html>