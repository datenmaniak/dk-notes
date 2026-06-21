<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @php
          use App\Models\UserSetting;
          $darkClass = '';
          
          if (Auth::check()) {
              $tema = UserSetting::getValue(Auth::id(), 'tema', 'auto');
          } else {
              $tema = 'auto';
          }
          
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

        <title>{{ config('app.name', 'dk-notes') }}</title>

        <!-- Font Awesome -->
        <script src="https://kit.fontawesome.com/798dc59432.js" crossorigin="anonymous"></script>

        <!-- Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Google Fonts: Roboto -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    </head>
    
    <body class="font-sans antialiased page-daten text-daten-primary bg-red-600">
        <div class="page-daten flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-daten-secondary" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 card-daten shadow-xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>