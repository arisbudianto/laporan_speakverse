import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {

    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {

            fontFamily: {
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
            },

            colors: {

                primary: {
                    DEFAULT: '#22d3ee',
                    dark: '#0891b2',
                },

                dark: {
                    DEFAULT: '#050816',
                    soft: '#0b1120',
                    card: '#111827',
                },

            },

            boxShadow: {
                glow: '0 0 30px rgba(34,211,238,0.25)',
            },

            borderRadius: {
                '4xl': '2rem',
            },

        },
    },

    plugins: [forms],
};