# Filtrar por categorias

**Especificacion de lo deseado**
Al elegirse una categoria de una lista presentada por el modal, se debe mostrar las notas/registros asociados a la categoria elegida y asociadas al usuario actual.

**Fallo encontrado:**
La funcion de filtrar por categoria funciona solo para un usuario. Cuando varios usuarios tienen registros que coinciden con el mismo nombre/slug de categoria de otros usuarios, el filtrado por categoria no funciona.


## llamado de la funcion de filtrar por categorias **index.blade.php**
```php
 {{-- ============================================ --}}
        {{-- MODAL: FILTRAR POR CATEGORÍA --}}
        {{-- ============================================ --}}
        <div id="filterModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center transition-all">
            <div class="bg-daten-card rounded-xl border border-daten shadow-2xl max-w-md w-full mx-4 overflow-hidden flex flex-col">
                <div class="p-6 border-b border-daten flex items-center gap-2.5 text-daten-primary">
                    <i class="fa-solid fa-filter text-brand-glow text-lg"></i>
                    <h3 class="text-xl font-bold tracking-tight">Filtrar por categoría</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="space-y-1.5 overflow-y-auto max-h-64 pr-1">
                        <a href="{{ route('notes.filter') }}?page={{ $notes->currentPage() }}" 
                           class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-brand-glow/5 text-daten-secondary hover:text-brand-glow font-medium text-sm rounded-lg transition-all border border-transparent hover:border-brand-glow/10">
                            <i class="fa-solid fa-border-all text-xs opacity-60"></i> Todas las notas
                        </a>
                   
                        <!-- List all categories that belong to the authenticated user -->
                        @foreach(auth()->user()->categories as $category)
                        
                            <div class="flex items-center justify-between group/row">
                                <a href="{{ route('notes.filter', $category->slug) }}?page={{ $notes->currentPage() }}" 
                                class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-brand-glow/5 text-daten-secondary hover:text-brand-glow font-medium text-sm rounded-lg flex-1 transition-all border border-transparent hover:border-brand-glow/10">
                                    <i class="fa-solid fa-folder text-xs opacity-40 group-hover/row:text-brand-glow group-hover/row:opacity-100 transition-all"></i> {{ $category->name }}
                                </a>
                                <form method="POST" action="{{ route('categories.destroy', $category) }}" 
                                    onsubmit="return confirm('¿Eliminar categoría \"{{ $category->name }}\"?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-daten-muted hover:text-red-600 dark:hover:text-red-400 px-3 py-2 transition-colors" title="Eliminar categoría">
                                        <i class="fa-solid fa-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                    <div class="pt-4 border-t  text-right">
                        <button onclick="closeModal()" 
                                class="px-4 py-2 border border-daten text-daten-secondary bg-daten-card hover:bg-brand-glow/10 hover:text-brand-glow rounded-lg text-sm font-medium transition-all duration-200">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
```





## NoteController.php

**function:** note.filter

Codigo fuente PHP
```php
 public function filter($categorySlug = null)
    {

        // paginador
        // $perPage = request()->input('per_page', 5); // Configurable desde la BD
        $perPage = UserSetting::getValue(Auth::id(), 'notas_por_pagina', 5);

        $user = User::find(Auth::id());

        if ($categorySlug) {
            $category = Category::where('slug', $categorySlug)->firstOrFail();
            $totalNotas = $user->notes()->where('category_id', $category->id)->count();
            // $notes = $user->notes()->where('category_id', $category->id)->with('category')->get();
            $notes = $user->notes()->where('category_id', $category->id)->with('category')->paginate($perPage);  // ✅ Correcto
        } else {
            $totalNotas = $user->notes()->count();
            // $notes = $user->notes()->with('category')->get();
            $notes = $user->notes()->with('category')->paginate($perPage);
        }

        // $totalCategorias = Category::count();
        $totalCategorias = Category::where('user_id', Auth::id())->count();
        $categoriasConNotas = Category::withCount('notes')->get();

        // 🚀 NUEVO: Cargar todas las etiquetas válidas para que la cabecera no se rompa al filtrar categorías
        $tagsWithCount = Tag::whereNull('user_id')
            ->orWhere('user_id', Auth::id())
            ->withCount(['notes' => function ($query) {
                $query->where('notes.user_id', Auth::id());
            }])
            ->orderBy('name')
            ->get();

        // Guardar página y filtro actual en sesión
        session(['last_notes_page' => request()->input('page', 1)]);
        session(['last_notes_filter' => $categorySlug]);

        return view('notes.index', compact('notes', 'totalNotas', 'totalCategorias', 'categoriasConNotas', 'tagsWithCount'));
    }
```
