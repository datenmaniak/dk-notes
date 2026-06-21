<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @php
          use App\Models\UserSetting;
          $darkClass = '';
          
          // Verificamos si hay una sesión activa (por si acaso)
          if (Auth::check()) {
              $tema = UserSetting::getValue(Auth::id(), 'tema', 'auto');
          } else {
              $tema = 'auto'; // Si no está logueado, se asume 'auto'
          }
          
          // Aplicamos la clase correspondiente
          if ($tema == 'dark') {
              $darkClass = 'dark';
          } elseif ($tema == 'auto' && isset($_COOKIE['theme_preference'])) {
              $darkClass = $_COOKIE['theme_preference'] === 'dark' ? 'dark' : '';
          }
      @endphp
      class="{{ $darkClass }}">
    <!-- <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head> -->
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'dk-notes') }}</title>

    <script src="https://kit.fontawesome.com/798dc59432.js" crossorigin="anonymous"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
    <!-- <body class="font-sans text-gray-900 antialiased"> -->
    <body class="font-sans antialiased bg-(--bg-primary) text-(--text-primary)">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 text-(--text-secondary)">
            <div>
                <a href="/">
                    <!-- <x-application-logo class="w-20 h-20 fill-current text-gray-500" /> -->
                    <x-application-logo class="w-20 h-20 fill-current text-(--text-secondary)" />
                </a>
            </div>

            <div 
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-(--bg-card) border border-(--border-color) shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
