# Total de etiquetas en el cuadro de estadisticas

Mostrar en la seccion de estadisticas, tarjetas de etiquetas con su respectivo 
 total de notas


## Especificaciones

- Mostrar tarjetas separadas por etiquetas.
- Agregar a cada tarjeta un icono de Font Awesome.
- Asignar el icono considerando la semantica de cada etiqueta.


## Seccion de estadisticas

```php
      
        {{-- ============================================ --}}
        {{-- TARJETAS DE ESTADÍSTICAS --}}
        {{-- ============================================ --}}
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
```



