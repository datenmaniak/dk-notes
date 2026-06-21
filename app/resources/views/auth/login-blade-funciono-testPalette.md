<x-guest-layout>
    <div style="background-color: var(--bg-primary); min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem;">
        <!-- login-blade-funciono-testPalette -->
        <!-- BLOQUE DE TESTING -->
        <div style="max-width: 28rem; width: 100%; margin-bottom: 1.5rem; padding: 1rem; background-color: var(--bg-card); border: 2px solid #7700F0; border-radius: 0.75rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 0.875rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="width: 0.5rem; height: 0.5rem; border-radius: 9999px; background-color: #7700F0; display: inline-block;"></span>
                TEST: Paleta datenmaniak
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.75rem;">
                <div style="padding: 0.5rem; border-radius: 0.25rem; background-color: var(--bg-primary); border: 1px solid var(--border-color);">
                    <span style="color: var(--text-primary);">bg-primary</span>
                </div>
                <div style="padding: 0.5rem; border-radius: 0.25rem; background-color: var(--bg-card); border: 1px solid var(--border-color);">
                    <span style="color: var(--text-primary);">bg-card</span>
                </div>
                <div style="padding: 0.5rem; border-radius: 0.25rem; background-color: var(--bg-input); border: 1px solid var(--border-color);">
                    <span style="color: var(--text-secondary);">bg-input</span>
                </div>
                <div style="padding: 0.5rem; border-radius: 0.25rem; background-color: rgba(119,0,240,0.1); border: 1px solid rgba(119,0,240,0.2);">
                    <span style="color: #7700F0; font-weight: 600;">brand-glow</span>
                </div>
                <div style="padding: 0.5rem; border-radius: 0.25rem; background-color: rgba(125,46,255,0.1); border: 1px solid rgba(125,46,255,0.2);">
                    <span style="color: #7d2eff; font-weight: 600;">brand-accent</span>
                </div>
                <div style="padding: 0.5rem; border-radius: 0.25rem; background-color: rgba(163,102,255,0.1); border: 1px solid rgba(163,102,255,0.2);">
                    <span style="color: #a366ff; font-weight: 600;">brand-muted</span>
                </div>
                <div style="grid-column: span 2; padding: 0.5rem; border-radius: 0.25rem; border: 2px solid var(--border-color); background-color: var(--bg-primary);">
                    <span style="color: var(--text-muted); font-size: 0.75rem;">border-color: <span style="font-family: monospace;">[--border-color]</span></span>
                </div>
            </div>
            
            <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem;">
                <button style="flex: 1; padding: 0.375rem 0.75rem; background-color: #7700F0; color: white; font-size: 0.75rem; font-weight: 500; border-radius: 0.5rem; border: none; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-check" style="margin-right: 0.25rem;"></i> brand-glow
                </button>
                <button style="flex: 1; padding: 0.375rem 0.75rem; background-color: transparent; color: var(--text-secondary); font-size: 0.75rem; font-weight: 500; border-radius: 0.5rem; border: 1px solid var(--border-color); cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-times" style="margin-right: 0.25rem;"></i> secundario
                </button>
            </div>
            
            <div style="margin-top: 0.5rem; text-align: center; font-size: 0.625rem; color: var(--text-muted); border-top: 1px solid var(--border-color); padding-top: 0.5rem;">
                <i class="fas fa-circle" style="margin-right: 0.25rem; color: var(--bg-primary);"></i>
                Modo: <span id="modo-indicador" style="font-family: monospace;">claro</span>
            </div>
        </div>

        <!-- LOGIN ORIGINAL -->
        <div class="w-full sm:max-w-md px-6 py-4 shadow-xl overflow-hidden sm:rounded-lg" 
             style="background-color: var(--bg-card); border: 1px solid var(--border-color);">
            
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="display: inline-flex; width: 4rem; height: 4rem; background-color: #7700F0; border-radius: 9999px; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fas fa-sticky-note" style="font-size: 1.5rem; color: white;"></i>
                </div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">
                    Bienvenido
                </h1>
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1.5rem;">
                    <i class="fas fa-edit" style="margin-right: 0.25rem;"></i> Gestiona tus notas de forma eficiente
                </p>
            </div>
            
            @if ($errors->any())
                <div style="margin-bottom: 1rem; padding: 0.75rem; background-color: #FEE2E2; border: 1px solid #FECACA; border-radius: 0.5rem;">
                    @foreach ($errors->all() as $error)
                        <p style="color: #DC2626; font-size: 0.875rem; display: flex; align-items: center;">
                            <i class="fas fa-exclamation-circle" style="margin-right: 0.5rem;"></i> {{ $error }}
                        </p>
                    @endforeach
                </div>
            @endif
            
            @if (session('status'))
                <div style="margin-bottom: 1rem; padding: 0.75rem; background-color: #DCFCE7; border: 1px solid #BBF7D0; border-radius: 0.5rem;">
                    <p style="color: #16A34A; font-size: 0.875rem; display: flex; align-items: center;">
                        <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i> {{ session('status') }}
                    </p>
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div style="margin-bottom: 1rem;">
                    <label for="email" style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--text-secondary); margin-bottom: 0.25rem;">
                        <i class="fas fa-envelope" style="margin-right: 0.25rem;"></i> Correo electrónico
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background-color: var(--bg-input); color: var(--text-primary); outline: none; transition: all 0.2s;">
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label for="password" style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--text-secondary); margin-bottom: 0.25rem;">
                        <i class="fas fa-lock" style="margin-right: 0.25rem;"></i> Contraseña
                    </label>
                    <input id="password" type="password" name="password" required
                           style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background-color: var(--bg-input); color: var(--text-primary); outline: none; transition: all 0.2s;">
                </div>
                
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="remember" 
                               style="border-radius: 0.25rem; border-color: var(--border-color); color: #7700F0;">
                        <span style="margin-left: 0.5rem; font-size: 0.875rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="margin-right: 0.25rem;"></i> Recordarme
                        </span>
                    </label>
                    
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" 
                           style="font-size: 0.875rem; color: #7700F0; text-decoration: none;">
                            <i class="fas fa-key" style="margin-right: 0.25rem;"></i> ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>
                
                <button type="submit" 
                        style="width: 100%; padding: 0.5rem 1rem; background-color: #7700F0; color: white; font-weight: 600; border: none; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i> Iniciar sesión
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <p style="font-size: 0.875rem; color: var(--text-secondary);">
                    <i class="fas fa-user-plus" style="margin-right: 0.25rem;"></i> ¿No tienes cuenta?
                    <a href="{{ route('register') }}" 
                       style="color: #7700F0; font-weight: 500; text-decoration: none;">
                        Regístrate aquí
                    </a>
                </p>
            </div>

            <div style="text-align: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <p style="font-size: 0.75rem; color: var(--text-muted);">
                    <i class="fas fa-code" style="margin-right: 0.25rem;"></i> datenmaniak · v{{ config('app.version', '1.0') }}
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>