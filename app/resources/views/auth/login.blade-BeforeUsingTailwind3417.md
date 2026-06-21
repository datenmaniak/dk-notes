<x-guest-layout>
    <!-- login.blade-BeforeUsingTailwind3417 -->
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[--bg-primary] transition-colors duration-300">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-[--bg-card] border border-[--border-color] shadow-xl overflow-hidden sm:rounded-lg transition-colors duration-300">
            
            {{-- Logo o ícono --}}
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-brand-glow rounded-full flex items-center justify-center shadow-lg shadow-brand-glow/20">
                    <i class="fas fa-sticky-note text-2xl text-white"></i>
                </div>
            </div>
            
            {{-- Título --}}
            <h1 class="text-2xl font-bold text-center text-[--text-primary] mb-2 transition-colors duration-300">
                Bienvenido
            </h1>
            <p class="text-center text-[--text-secondary] text-sm mb-6 transition-colors duration-300">
                <i class="fas fa-edit mr-1"></i> Gestiona tus notas de forma eficiente
            </p>
            
            {{-- Mensajes de error --}}
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i> {{ $error }}
                        </p>
                    @endforeach
                </div>
            @endif
            
            {{-- Mensaje de éxito (recuperación, etc.) --}}
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <p class="text-sm text-green-600 dark:text-green-400 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('status') }}
                    </p>
                </div>
            @endif
            
            {{-- Formulario --}}
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                {{-- Email --}}
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-[--text-secondary] mb-1 transition-colors duration-300">
                        <i class="fas fa-envelope mr-1"></i> Correo electrónico
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-3 py-2 rounded-lg border border-[--border-color] bg-[--bg-input] text-[--text-primary] placeholder:text-[--text-muted] focus:outline-none focus:ring-2 focus:ring-brand-glow/30 focus:border-brand-glow transition-colors duration-300">
                </div>
                
                {{-- Contraseña --}}
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-[--text-secondary] mb-1 transition-colors duration-300">
                        <i class="fas fa-lock mr-1"></i> Contraseña
                    </label>
                    <input id="password" type="password" name="password" required
                           class="w-full px-3 py-2 rounded-lg border border-[--border-color] bg-[--bg-input] text-[--text-primary] placeholder:text-[--text-muted] focus:outline-none focus:ring-2 focus:ring-brand-glow/30 focus:border-brand-glow transition-colors duration-300">
                </div>
                
                {{-- Recordarme --}}
                <div class="flex items-center justify-between mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" 
                               class="rounded border-[--border-color] text-brand-glow focus:ring-brand-glow/30 focus:ring-2 transition-colors duration-300">
                        <span class="ml-2 text-sm text-[--text-secondary] transition-colors duration-300">
                            <i class="fas fa-check-circle mr-1"></i> Recordarme
                        </span>
                    </label>
                    
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" 
                           class="text-sm text-brand-glow hover:text-brand-accent transition-colors duration-200">
                            <i class="fas fa-key mr-1"></i> ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>
                
                {{-- Botón --}}
                <button type="submit" 
                        class="w-full bg-brand-glow hover:bg-brand-accent text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:shadow-lg hover:shadow-brand-glow/20 active:scale-[0.98]">
                    <i class="fas fa-sign-in-alt mr-2"></i> Iniciar sesión
                </button>
            </form>
            
            {{-- Enlace a registro --}}
            <div class="text-center mt-6">
                <p class="text-sm text-[--text-secondary] transition-colors duration-300">
                    <i class="fas fa-user-plus mr-1"></i> ¿No tienes cuenta?
                    <a href="{{ route('register') }}" 
                       class="text-brand-glow hover:text-brand-accent font-medium transition-colors duration-200">
                        Regístrate aquí
                    </a>
                </p>
            </div>

            {{-- Versión --}}
            <div class="text-center mt-6 pt-4 border-t border-[--border-color] transition-colors duration-300">
                <p class="text-xs text-[--text-muted] transition-colors duration-300">
                    <i class="fas fa-code mr-1"></i> datenmaniak · v{{ config('app.version', '1.0') }}
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>