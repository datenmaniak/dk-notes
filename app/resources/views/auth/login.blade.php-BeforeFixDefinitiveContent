<x-guest-layout>
    <div class="page-daten flex flex-col items-center justify-center p-6">
        
        <!-- BLOQUE DE TESTING -->
        <div class="w-full max-w-md mb-6 p-4 card-daten border-2 border-brand-glow shadow-lg">
            <h3 class="text-sm font-bold text-daten-primary mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-brand-glow inline-block animate-pulse"></span>
                TEST: Paleta datenmaniak
            </h3>
            
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="p-2 rounded bg-daten-primary border-daten">
                    <span class="text-daten-primary">bg-primary</span>
                </div>
                <div class="p-2 rounded bg-daten-card border-daten">
                    <span class="text-daten-primary">bg-card</span>
                </div>
                <div class="p-2 rounded bg-daten-input border-daten">
                    <span class="text-daten-secondary">bg-input</span>
                </div>
                <div class="p-2 rounded bg-brand-glow/10 border border-brand-glow/20">
                    <span class="text-brand-glow font-semibold">brand-glow</span>
                </div>
                <div class="p-2 rounded bg-brand-accent/10 border border-brand-accent/20">
                    <span class="text-brand-accent font-semibold">brand-accent</span>
                </div>
                <div class="p-2 rounded bg-brand-muted/10 border border-brand-muted/20">
                    <span class="text-brand-muted font-semibold">brand-muted</span>
                </div>
                <div class="col-span-2 p-2 rounded border-2 border-daten bg-daten-primary">
                    <span class="text-daten-muted text-xs">border-color</span>
                </div>
            </div>
            
            <div class="mt-3 flex gap-2">
                <button class="flex-1 py-1.5 px-3 bg-brand-glow hover:bg-brand-accent text-white text-xs font-medium rounded-lg transition-all">
                    <i class="fas fa-check mr-1"></i> brand-glow
                </button>
                <button class="flex-1 py-1.5 px-3 border-daten hover:bg-daten-input text-daten-secondary text-xs font-medium rounded-lg transition-all">
                    <i class="fas fa-times mr-1"></i> secundario
                </button>
            </div>
            
            <div class="mt-2 text-center text-[10px] text-daten-muted border-t border-daten pt-2">
                <i class="fas fa-circle mr-1" style="color: var(--bg-primary);"></i>
                Modo: <span id="modo-indicador" class="font-mono">claro</span>
                <span class="mx-1">·</span>
                <i class="fas fa-{{ Auth::check() ? 'user' : 'user-secret' }} mr-1"></i>
                {{ Auth::check() ? 'Autenticado' : 'Invitado' }}
            </div>
        </div>

        <!-- LOGIN ORIGINAL -->
        <div class="w-full sm:max-w-md px-6 py-4 card-daten shadow-xl">
            
            <div class="text-center mb-6">
                <div class="w-16 h-16 mx-auto bg-brand-glow rounded-full flex items-center justify-center mb-4 shadow-lg shadow-brand-glow/20">
                    <i class="fas fa-sticky-note text-2xl text-white"></i>
                </div>
                <h1 class="text-2xl font-bold text-daten-primary mb-2">
                    Bienvenido
                </h1>
                <p class="text-daten-secondary text-sm">
                    <i class="fas fa-edit mr-1"></i> Gestiona tus notas de forma eficiente
                </p>
            </div>
            
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i> {{ $error }}
                        </p>
                    @endforeach
                </div>
            @endif
            
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <p class="text-sm text-green-600 dark:text-green-400 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('status') }}
                    </p>
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-daten-secondary mb-1">
                        <i class="fas fa-envelope mr-1"></i> Correo electrónico
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-3 py-2 rounded-lg input-daten placeholder:text-daten-muted focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-daten-secondary mb-1">
                        <i class="fas fa-lock mr-1"></i> Contraseña
                    </label>
                    <input id="password" type="password" name="password" required
                           class="w-full px-3 py-2 rounded-lg input-daten placeholder:text-daten-muted focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30">
                </div>
                
                <div class="flex items-center justify-between mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" 
                               class="rounded border-daten text-brand-glow focus:ring-brand-glow/30">
                        <span class="ml-2 text-sm text-daten-secondary">
                            <i class="fas fa-check-circle mr-1"></i> Recordarme
                        </span>
                    </label>
                    
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" 
                           class="text-sm text-brand-glow hover:text-brand-accent transition-colors">
                            <i class="fas fa-key mr-1"></i> ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>
                
                <button type="submit" 
                        class="w-full bg-brand-glow hover:bg-brand-accent text-white font-semibold py-2 px-4 rounded-lg transition-all hover:shadow-lg hover:shadow-brand-glow/20 active:scale-[0.98]">
                    <i class="fas fa-sign-in-alt mr-2"></i> Iniciar sesión
                </button>
            </form>
            
            <div class="text-center mt-6">
                <p class="text-sm text-daten-secondary">
                    <i class="fas fa-user-plus mr-1"></i> ¿No tienes cuenta?
                    <a href="{{ route('register') }}" 
                       class="text-brand-glow hover:text-brand-accent font-medium transition-colors">
                        Regístrate aquí
                    </a>
                </p>
            </div>

            <div class="text-center mt-6 pt-4 border-t border-daten">
                <p class="text-xs text-daten-muted">
                    <i class="fas fa-code mr-1"></i> datenmaniak · v{{ config('app.version', '1.0') }}
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>