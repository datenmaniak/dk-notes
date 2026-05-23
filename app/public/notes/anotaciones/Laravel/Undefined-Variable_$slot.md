# ErrorException - Undefined variable $slot



> # Se presenta este error, a pesar que he ajustado la sentencia a  @section('slot') en el archivo show.blade.php   



```htm
ErrorException
resources/views/layouts/app.blade.php:32

Undefined variable $slot
```





El error ocurre porque estás mezclando dos enfoques distintos de Blade: **layouts con `@section/@yield`** y **componentes con `$slot`**. Si tu `show.blade.php` usa `@section('slot')`, entonces en `layouts/app.blade.php` no debes imprimir `{{ $slot }}`, sino `@yield('slot')` o, más comúnmente, `@yield('content')`.[^1_1][^1_2]

## Qué está pasando

`$slot` solo existe cuando el layout se usa como componente, por ejemplo con `<x-app-layout>...</x-app-layout>`. Si tu vista extiende un layout clásico con `@extends('layouts.app')`, entonces el contenido se define con `@section(...)` y se muestra con `@yield(...)`.[^1_3][^1_4]

## Cómo corregirlo

Tienes dos opciones:

- **Opción 1: mantener `@extends`**
    - En `resources/views/layouts/app.blade.php`, cambia `{{ $slot }}` por algo como `@yield('content')`.[^1_2][^1_1]
    - En `show.blade.php`, usa:

```blade
@extends('layouts.app')

@section('content')
    ...
@endsection
```

- **Opción 2: usar componente Blade**
    - Mantén `{{ $slot }}` en el layout.
    - En tu vista usa `<x-app-layout> ... </x-app-layout>` en lugar de `@extends`.[^1_4][^1_3]


## Recomendación práctica

Si tu archivo está en `resources/views/layouts/app.blade.php`, lo más probable es que debas usar la versión clásica con `@yield('content')` en el layout y `@section('content')` en la vista.[^1_1][^1_2]

## Verificación rápida

Revisa estas dos líneas:

- En `app.blade.php`: debe haber `@yield('content')`, no `{{ $slot }}`.[^1_1]
- En `show.blade.php`: debe haber `@section('content')`, no `@section('slot')`.[^1_2]

Si el proyecto es Laravel Breeze o Jetstream, también vale la pena confirmar si estás usando un layout de componente (`resources/views/components/app-layout.blade.php`) en vez del layout clásico.[^1_4][^1_2]
<span style="display:none">[^1_10][^1_5][^1_6][^1_7][^1_8][^1_9]</span>
