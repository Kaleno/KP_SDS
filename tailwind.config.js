import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', 'Georgia', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                teal: {
                    50: '#f3f8f6',
                    100: '#dceee6',
                    200: '#b9ddd0',
                    300: '#8bc5b3',
                    400: '#5aa893',
                    500: '#3d8b78',
                    600: '#2f7061',
                    700: '#275a50',
                    800: '#224942',
                    900: '#1d3c37',
                    950: '#0e221f',
                },
                gold: {
                    50: '#fbf7ee',
                    100: '#f4ead0',
                    200: '#e8d4a0',
                    300: '#dbb86d',
                    400: '#d0a04a',
                    500: '#c08634',
                    600: '#aa6b2b',
                    700: '#8d5126',
                    800: '#744225',
                    900: '#603821',
                },
                cream: {
                    50: '#fbfaf6',
                    100: '#f6f3ea',
                    200: '#ece6d4',
                },
            },
            boxShadow: {
                soft: '0 1px 2px rgba(14, 34, 31, 0.04), 0 10px 28px -12px rgba(14, 34, 31, 0.12)',
                lift: '0 12px 40px -16px rgba(14, 34, 31, 0.22)',
                glow: '0 16px 40px -12px rgba(39, 90, 80, 0.35)',
            },
            borderRadius: {
                '2.5xl': '1.25rem',
            },
        },
    },

    plugins: [forms],
};
