import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Roboto', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Tu paleta personalizada del playground
                brand: {
                    glow: '#7700F0',
                    deep: '#1A0B2E',
                    darkBg: '#0A0A0F',
                    lightBg: '#F9F7F1',
                    accent: '#7d2eff',
                    muted: '#a366ff',
                },
                // Paleta de Etiquetas (Labels)
                // tag: {
                // idea: '#F49027',        //  Creatividad
                // aplicable: '#09bb68',   // Listo para usar
                // // pendiente: '#4F8AA0',   //  - En espera
                // pendiente: '#EDF0000',   //  - En espera
                // urgente: '#F03428',     //  Prioridad
                // readlater: '#000BF0',   // Guardar para después
                // probar: '#2EA300',      // claro - Pruebas
                // sin_categorizar: '#64748B', // Gris - Neutro 
                // },
                tag: {
                    idea: '#7C3AED', // Lila Profundo
                    aplicable: '#059669', // Esmeralda
                    pendiente: '#D97706', // Ámbar / Oro
                    urgente: '#DC2626', // Rojo Alerta
                    readlater: '#2563EB', // Azul Índigo
                    probar: '#1f8766', // Menta Claro
                    corregir: '#4B5563', // Gris Slate
                },
                // Colores Compuestos (paleta extendida)
                extended: {
                    green: '#46F000',
                    yellow: '#EDF000',
                    blue: '#000BF0',
                    orange: '#F04C00',
                    lilac: '#67349B',
                    teal: '#00F0D7',
                }
            },
        },
    },

    plugins: [forms, typography],
};