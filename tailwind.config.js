import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/*.php',
        './app/Livewire/**/*.php'
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'custom': {
                    '50': '#f0f0f5',
                    '100': '#e1e1eb',
                    '200': '#c3c3d7',
                    '300': '#a5a5c3',
                    '400': '#8787af',
                    '500': '#69699b',
                    '600': '#5a5a80',
                    '700': '#4b4b66',
                    '800': '#3c3c4c',
                    '900': '#2d2d33',
                }
            }
        },
    },

    plugins: [forms, typography],
};
