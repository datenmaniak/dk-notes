<!DOCTYPE html>
<!-- guest.blade-beforeUsingTailwind3417 -->
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
    
    <body class="font-sans antialiased bg-[--bg-primary] text-[--text-primary] transition-colors duration-300">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-0 sm:pt-0 bg-[--bg-primary] transition-colors duration-300">
            <!-- Logo Laravel -->
            <!-- <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-[--text-secondary]" />
                </a>
            </div> -->

            <!-- <div class="w-full sm:max-w-lg mt-6 px-6 py-4 bg-[--bg-card] border border-[--border-color] shadow-xl overflow-hidden sm:rounded-lg transition-colors duration-300"> -->
             <div class="w-full sm:max-w-lg card-daten shadow-xl"> 
            {{ $slot }}
            </div>


            <!-- Footer de versión -->
            <!-- <div class="mt-6 text-center">
                <p class="text-xs text-[--text-muted] transition-colors duration-300">
                    <i class="fas fa-code mr-1"></i> datenmaniak · v{{ config('app.version', '1.0') }}
                </p>
            </div> -->
        </div>
         <!-- ============================================ -->
            <!-- FOOTER CON REDES SOCIALES Y URL              -->
            <!-- ============================================ -->
            <div class="w-full  text-center space-y-1.5 flex-shrink-0">
                <!-- Enlaces a Redes Sociales -->
                <div class="flex items-center justify-center gap-5">
                    <a href="https://github.com/tuusuario" target="_blank" 
                       class="text-daten-muted hover:text-brand-glow hover:scale-110 transition-all duration-200 text-base inline-block">
                        <i class="fab fa-github"></i>
                        <span class="sr-only">GitHub</span>
                    </a>
                    <a href="https://linkedin.com/in/tuusuario" target="_blank" 
                       class="text-daten-muted hover:text-brand-glow hover:scale-110 transition-all duration-200 text-base inline-block">
                        <i class="fab fa-linkedin-in"></i>
                        <span class="sr-only">LinkedIn</span>
                    </a>
                    <a href="https://www.datenmaniak.com" target="_blank" 
                       class="text-daten-muted hover:text-brand-glow hover:scale-110 transition-all duration-200 text-base inline-block">
                        <i class="fas fa-globe"></i>
                        <span class="sr-only">Sitio Web</span>
                    </a>
                </div>
                
                <!-- URL y Versión -->
                <div>
                    <p class="text-[11px] text-daten-muted tracking-wide">
                        <i class="fas fa-code mr-1"></i> 
                        <a href="https://www.datenmaniak.com" target="_blank" 
                           class="hover:text-brand-glow transition-colors duration-200">
                            datenmaniak.com
                        </a>
                        <span class="mx-1.5 text-daten-muted/30">|</span>
                        <span class="font-mono font-semibold text-brand-muted/80">v1.56.0-beta</span>
                    </p>
                    <p class="text-[10px] text-daten-muted/50 mt-0.5">
                        &copy; {{ date('Y') }} datenmaniak · Todos los derechos reservados
                    </p>
                </div>
            </div>

        </div>

    </body>
</html>