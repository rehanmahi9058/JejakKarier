import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: { sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans] },
            colors: { brand: { DEFAULT: '#5b3df5', 50: '#f4f2ff', 100: '#ebe7ff', 600: '#4a2fd9', 900: '#1a0f4d' } },
        },
    },
    plugins: [forms],
};