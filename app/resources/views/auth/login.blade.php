<x-guest-layout>
    <div class="page-daten flex flex-col items-center justify-center">

        <!-- LOGIN ORIGINAL - Solo ajuste de ancho -->
        <!-- <div class="w-full max-w-4xl px-4 sm:px-2 py-4"> -->
        <div class="w-full max-w-3xl px-4 sm:px-2 py-4">
            <div class="w-full sm:max-w-lg mx-auto px-6 py-8 card-daten shadow-xl">
                
                <div class="text-center mb-6">
                    <div class="w-16 h-16 mx-auto bg-brand-glow rounded-full flex items-center justify-center mb-4 shadow-lg shadow-brand-glow/20">
                        <i class="fas fa-sticky-note text-2xl text-white"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-daten-primary mb-3">
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
                            <i class="fas fa-envelope mr-1 text-brand-glow text-xl"></i> Correo electrónico
                        </label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full px-3 py-2 rounded-lg input-daten placeholder:text-daten-muted focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30">
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-daten-secondary mb-1">
                            <i class="fas fa-lock mr-1 text-brand-glow text-md"></i> Contraseña
                        </label>
                        <input id="password" type="password" name="password" required
                               class="w-full px-3 py-2 rounded-lg input-daten placeholder:text-daten-muted focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30">
                    </div>
                    
                    <div class="flex items-center justify-between mb-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" 
                                   class="rounded border-daten text-brand-glow focus:ring-brand-glow/30">
                            <span class="ml-2 text-sm text-daten-secondary">
                                <i class="fas fa-check-circle mr-1 text-brand-glow text-sm"></i> Recordarme
                            </span>
                        </label>
                        
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" 
                               class="text-sm text-brand-glow hover:text-brand-accent transition-colors">
                                <i class="fas fa-key mr-1 text-brand-glow text-sm"></i> ¿Olvidaste tu contraseña?
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
                        <i class="fas fa-user-plus mr-1 text-brand-glow text-sm"></i> ¿No tienes cuenta?
                        <a href="{{ route('register') }}" 
                           class="text-brand-glow hover:text-brand-accent font-medium transition-colors">
                            Regístrate aquí
                        </a>
                    </p>
                </div>
                
                <!-- ============================================ -->
                <!-- VERSIÓN MOVIDA DENTRO DEL FORMULARIO       -->
                <!-- ============================================ -->
                <div class="mt-3 pt-1 border-t border-daten/20 text-center">
                    <p class="text-xs text-daten-muted tracking-wide">
                        <!-- <i class="fas fa-code mr-1"></i>  -->
                        <span class="font-mono font-semibold text-brand-muted/80">v1.56.2</span>
                    </p>
                </div>
                
            </div>
        </div>
        
        <!-- EL FOOTER EXTERNO YA NO ESTÁ AQUÍ (lo maneja guest-layout) -->
        
    </div>
</x-guest-layout>