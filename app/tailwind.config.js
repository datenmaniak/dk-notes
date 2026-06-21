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
            },
        },
    },

    plugins: [forms, typography],
};