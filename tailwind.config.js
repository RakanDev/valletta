import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: ['./resources/**/*.blade.php', './resources/**/*.js'],
    theme: {
        extend: {
            colors: {
                cream: { DEFAULT: '#F6F1E9', deep: '#EDE5D8' },
                ink: { DEFAULT: '#2A211B', soft: '#6B5D50' },
                gold: { DEFAULT: '#8B6914', light: '#B38B2E', pale: '#E9DDC3' },
                line: '#E2D8C8',
            },
            fontFamily: {
                sans: ['Tajawal', ...defaultTheme.fontFamily.sans],
                display: ['"Cormorant Garamond"', 'Tajawal', ...defaultTheme.fontFamily.serif],
            },
        },
    },
    plugins: [],
};
