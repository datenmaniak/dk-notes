# Recrear el codigo del contenedor

Se quiere distribuir las tarjetas:

- Total Notas
- Total Categorias
- Total Etiquetas

## Especificaciones

0. Reusar este diseño de tarjetas.
1. Mostrar todas las tarjetas de manera horizontal en pantalla grandes, de manera responsiva.
2. Utilizar icono de Font Awesome de manera semantica a cada tarjeta.
3. Mostrar las tarjteas de manera vertical en caso de dispositivos moviles y  que ocupe el 80 % del area.
4. Agregar un titulo asociado al contenido dentro del contenedor.
5. No alterar el codigo PHP actual.



## Extracto del codigo a refactorizar

```php
        {{-- ============================================ --}}
{{-- COMPONENTE SUPERIOR: ESTADÍSTICAS & NAVBAR DE ETIQUETAS (OPCIÓN 2) --}}
{{-- ============================================ --}}
<div class="space-y-4">
    
    {{-- Fila superior: Las 2 tarjetas estáticas y limpias --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
    </div>

    {{-- Tarjeta 3: Total Etiquetas (Misma densidad visual) --}}
    <div class="bg-daten-card rounded-xl border border-daten shadow-sm p-6 transition-all duration-300 hover:shadow-md">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-daten-secondary uppercase tracking-wider">Total Etiquetas</p>
                <p class="text-3xl font-bold text-daten-primary tracking-tight">{{ $tagsWithCount->count() }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-brand-glow/10 flex items-center justify-center text-brand-glow">
                <i class="fa-solid fa-tags text-xl"></i>
            </div>
        </div>
    </div>

</div>
```


