/* @tailwind base; */
@import "tailwindcss";
@config "../../tailwind.config.js";
@tailwind utilities;

/* beforeUsingTailwind3417 */

/* ============================================
   VARIABLES GLOBALES - Paleta datenmaniak
   ============================================ */

/* Modo Claro (por defecto) */
:root {
    --bg-primary: #F9F7FF;
    --border-color: rgba(119, 0, 240, 0.15);
    --bg-card: #ffffff;
    --bg-input: #f8fafc;
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --text-muted: #64748b;
}

/* Modo Oscuro */
.dark {
    --bg-primary: #0A0A0F;
    --border-color: rgba(119, 0, 240, 0.3);
    --bg-card: #121218;
    --bg-input: #1a1a2e;
    --text-primary: #f1f5f9;
    --text-secondary: #94a3b8;
    --text-muted: #64748b;
}

/* ============================================
   ESTILOS BASE
   ============================================ */

body {
    font-family: 'Roboto', sans-serif;
    background-color: var(--bg-primary);
    color: var(--text-primary);
    transition: background-color 0.3s ease, color 0.3s ease;
    min-height: 100vh;
}

/* ============================================
   ESTILOS PERSONALIZADOS - CONTENIDO DE NOTAS
   (Manteniendo tu código existente)
   ============================================ */
/* Estilos personalizados para el contenido de notas */
.note-content {
    white-space: pre-line;
}

.note-content ul {
    list-style-type: disc;
    margin-left: 1.5rem;
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}

.note-content li {
    margin-top: 0.25rem;
    margin-bottom: 0.25rem;
}

/* ============================================
   UTILIDADES PERSONALIZADAS
   Basado en la maqueta index2.php
   ============================================ */

    .card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .input-base {
        background-color: var(--bg-input);
        border: 1px solid var(--border-color);
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }

    .input-base:focus {
        border-color: #7700F0;
        box-shadow: 0 0 0 2px rgba(119, 0, 240, 0.3);
        outline: none;
    }