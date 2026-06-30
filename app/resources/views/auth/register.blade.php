<x-guest-layout>
    <div class="page-daten flex flex-col items-center justify-center ">       
        <!-- ============================================ -->
        <!-- REGISTRO PRINCIPAL                          -->
        <!-- ============================================ -->
        <!-- <div class="w-full max-w-lg card-daten shadow-xl p-6 md:p-8"> -->
         <!-- <div class="w-full max-w-3xl px-4 sm:px-2 py-4 card-daten shadow-xl"> -->
         <div class="w-min-full max-w-5xl card-daten shadow-xl p-6 md:p-8">
            
            <!-- Logo / Icono de la App -->
            <div class="text-center mb-6">
                <div class="w-16 h-16 mx-auto bg-brand-glow rounded-2xl flex items-center justify-center mb-3 shadow-lg shadow-brand-glow/30 transition-all hover:scale-105 duration-300">
                    <i class="fas fa-user-plus text-2xl text-white"></i>
                </div>
                <h1 class="text-2xl font-bold text-daten-primary mb-0.5 tracking-tight">
                    Crear cuenta
                </h1>
                <p class="text-daten-secondary text-xs font-light tracking-wide">
                    <i class="fas fa-edit mr-1"></i> Únete al Gestor de Notas de datenmaniak
                </p>
            </div>
            
            <!-- Mensajes de Error -->
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i> {{ $error }}
                        </p>
                    @endforeach
                </div>
            @endif
            
            <!-- Formulario de Registro -->
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf
                
                <!-- Nombre -->
                <div>
                    <label for="name" class="block text-sm font-medium text-daten-secondary mb-1">
                        <i class="fas fa-user mr-1"></i> Nombre completo
                    </label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                           class="w-full px-4 py-2.5 rounded-lg input-daten placeholder:text-daten-muted focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30 transition-all duration-200">
                </div>
                
                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-daten-secondary mb-1">
                        <i class="fas fa-envelope mr-1"></i> Correo electrónico
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2.5 rounded-lg input-daten placeholder:text-daten-muted focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30 transition-all duration-200">
                </div>
                
                <!-- Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-daten-secondary mb-1">
                        <i class="fas fa-lock mr-1"></i> Contraseña
                    </label>
                    <input id="password" type="password" name="password" required
                           class="w-full px-4 py-2.5 rounded-lg input-daten placeholder:text-daten-muted focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30 transition-all duration-200">
                </div>
                
                <!-- Confirmar Contraseña -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-daten-secondary mb-1">
                        <i class="fas fa-check-circle mr-1"></i> Confirmar contraseña
                    </label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           class="w-full px-4 py-2.5 rounded-lg input-daten placeholder:text-daten-muted focus:border-brand-glow focus:ring-2 focus:ring-brand-glow/30 transition-all duration-200">
                </div>
                
                <!-- Botón -->
                <button type="submit" 
                        class="w-full bg-brand-glow hover:bg-brand-accent text-white font-semibold py-2.5 px-4 rounded-lg transition-all duration-200 hover:shadow-lg hover:shadow-brand-glow/30 active:scale-[0.98]">
                    <i class="fas fa-user-plus mr-2"></i> Registrarse
                </button>
            </form>
            
            <!-- Enlace a Login -->
            <div class="text-center mt-6">
                <p class="text-sm text-daten-secondary">
                    <i class="fas fa-sign-in-alt mr-1"></i> ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}" 
                       class="text-brand-glow hover:text-brand-accent font-medium transition-colors duration-200">
                        Inicia sesión
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <!-- <div class="mt-4 text-center">
            <p class="text-xs text-daten-muted tracking-wide">
                <i class="fas fa-code mr-1"></i> 
                datenmaniak · 
                <span class="font-mono font-semibold text-brand-muted/80">v1.56.0-beta</span>
            </p>
        </div> -->

    </div>
</x-guest-layout>