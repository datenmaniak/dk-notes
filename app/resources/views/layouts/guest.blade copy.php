<!DOCTYPE html>
<!-- guest.blade.php - Versión Final -->
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
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'dk-notes') }}</title>

        <!-- Font Awesome Icons -->
        <script src="https://kit.fontawesome.com/798dc59432.js" crossorigin="anonymous"></script>

        <!-- Vite Assets -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Google Fonts: Roboto -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    </head>
    
    <body class="font-sans antialiased page-daten text-daten-primary">
        <!-- Contenedor principal: centrado vertical y horizontal -->
        <div class="min-h-screen flex flex-col items-center justify-center  page-daten">
            
            <!-- Contenedor del formulario (sin margen superior excesivo) ancho -->
            <div class="w-full sm:max-w-lg  card-daten shadow-xl">
                {{ $slot }}
            </div>

            <!-- ============================================ -->
            <!-- FOOTER CON REDES SOCIALES Y URL              -->
            <!-- ============================================ -->
            <div class="mt-6 text-center space-y-2">
                <!-- Enlaces a Redes Sociales -->
                <div class="flex items-center justify-center gap-4">
                    <a href="https://github.com/tuusuario" target="_blank" 
                       class="text-daten-muted hover:text-brand-glow transition-colors duration-200 text-lg">
                        <i class="fab fa-github"></i>
                        <span class="sr-only">GitHub</span>
                    </a>
                    <a href="https://linkedin.com/in/tuusuario" target="_blank" 
                       class="text-daten-muted hover:text-brand-glow transition-colors duration-200 text-lg">
                        <i class="fab fa-linkedin-in"></i>
                        <span class="sr-only">LinkedIn</span>
                    </a>
                    <a href="https://www.datenmaniak.com" target="_blank" 
                       class="text-daten-muted hover:text-brand-glow transition-colors duration-200 text-lg">
                        <i class="fas fa-globe"></i>
                        <span class="sr-only">Sitio Web</span>
                    </a>
                </div>
                
                <!-- URL y Versión -->
                <div class="space-y-0.5">
                    <p class="text-xs text-daten-muted tracking-wide">
                        <i class="fas fa-code mr-1"></i> 
                        <a href="https://www.datenmaniak.com" target="_blank" 
                           class="hover:text-brand-glow transition-colors duration-200">
                            datenmaniak.com
                        </a>
                        <span class="mx-1.5 text-daten-muted/30">|</span>
                        <span class="font-mono font-semibold text-brand-muted/80">v1.56.0-beta</span>
                    </p>
                    <p class="text-[10px] text-daten-muted/50">
                        &copy; {{ date('Y') }} datenmaniak · Todos los derechos reservados
                    </p>
                </div>
            </div>

        </div>
    </body>
</html>