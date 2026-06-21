<x-guest-layout>
    <!-- ============================================ -->
    <!-- BLOQUE DE TESTING - PALETA DATENMANIAK        -->
    <!-- ============================================ -->
    <div class="max-w-md mx-auto mb-6 p-4 bg-(--bg-card) border-2 border-brand-glow rounded-xl shadow-lg transition-colors duration-300">
        <h3 class="text-sm font-bold text-(--text-primary) mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-brand-glow animate-pulse"></span>
            TEST: Paleta datenmaniak
        </h3>
        
        <div class="grid grid-cols-2 gap-2 text-xs">
            <!-- Fondo primario -->
            <div class="p-2 rounded bg-(--bg-primary) border border-(--border-color)">
                <span class="text-(--text-primary)">bg-primary</span>
            </div>
            
            <!-- Tarjeta -->
            <div class="p-2 rounded bg-(--bg-card) border border-(--border-color)">
                <span class="text-(--text-primary)">bg-card</span>
            </div>
            
            <!-- Input -->
            <div class="p-2 rounded bg-(--bg-input) border border-(--border-color)">
                <span class="text-(--text-secondary)">bg-input</span>
            </div>
            
            <!-- Texto primario -->
            <div class="p-2 rounded bg-brand-glow/10 border border-brand-glow/20">
                <span class="text-brand-glow font-semibold">brand-glow</span>
            </div>
            
            <!-- Texto secundario -->
            <div class="p-2 rounded bg-brand-accent/10 border border-brand-accent/20">
                <span class="text-brand-accent font-semibold">brand-accent</span>
            </div>
            
            <!-- Texto muted -->
            <div class="p-2 rounded bg-brand-muted/10 border border-brand-muted/20">
                <span class="text-brand-muted font-semibold">brand-muted</span>
            </div>
            
            <!-- Borde -->
            <div class="col-span-2 p-2 rounded border-2 border-(--border-color) bg-(--bg-primary)">
                <span class="text-(--text-muted) text-xs">border-color: <span class="font-mono">[--border-color]</span></span>
            </div>
        </div>
        
        <!-- Botón de prueba -->
        <div class="mt-3 flex gap-2">
            <button class="flex-1 py-1.5 px-3 bg-brand-glow hover:bg-brand-accent text-white text-xs font-medium rounded-lg transition-all duration-200 hover:shadow-lg hover:shadow-brand-glow/20 active:scale-95">
                <i class="fas fa-check mr-1"></i> brand-glow
            </button>
            <button class="flex-1 py-1.5 px-3 border border-(--border-color) hover:bg-(--bg-input) text-(--text-secondary) text-xs font-medium rounded-lg transition-all duration-200">
                <i class="fas fa-times mr-1"></i> secundario
            </button>
        </div>
        
        <!-- Indicador de modo -->
        <div class="mt-2 text-center text-[10px] text-(--text-muted) border-t border-(--border-color) pt-2 transition-colors duration-300">
            <i class="fas fa-circle mr-1" style="color: var(--bg-primary);"></i>
            Modo: <span id="modo-indicador" class="font-mono">claro</span>
            <span class="mx-1">·</span>
            <i class="fas fa-{{ Auth::check() ? 'user' : 'user-secret' }} mr-1"></i>
            {{ Auth::check() ? 'Autenticado' : 'Invitado' }}
        </div>
    </div>
    <!-- ============================================ -->
    <!-- FIN BLOQUE DE TESTING                         -->
    <!-- ============================================ -->

    <!-- Tu contenido original del login -->
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-(--bg-card) border border-(--border-color) shadow-xl overflow-hidden sm:rounded-lg transition-colors duration-300">
        <!-- ... el resto del login ... -->
    </div>
</x-guest-layout>