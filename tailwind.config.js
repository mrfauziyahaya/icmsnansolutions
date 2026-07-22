import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                // Display face used by nansolutions.com.my (Kadence primary nav).
                display: ['Oswald', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Brand palette lifted from nansolutions.com.my (Kadence globals).
                brand: {
                    DEFAULT: '#2B6CB0',
                    dark:    '#215387',
                    ink:     '#1A202C',
                    slate:   '#2D3748',
                    body:    '#4A5568',
                    muted:   '#718096',
                    tint:    '#EDF2F7',
                    wash:    '#F7FAFC',
                },
            },
        },
    },

    plugins: [forms],
};
