const defaultTheme = require('tailwindcss/defaultTheme');
const colors = require('tailwindcss/colors');

module.exports = {
    darkMode: 'class',
    content: [
        './resources/assets/**/*.js',
        './resources/views/**/*.blade.php',
        './resources/views/**/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],

    safelist: [
        {
            pattern: /justify-(start|between|end)/,
            variants: ['sm', 'md', 'lg'],
        },
        {
            pattern: /items-(start|center|end)/,
            variants: ['sm', 'md', 'lg'],
        },
        {
            pattern: /flex-(1|auto|initial|none)/,
            variants: ['sm', 'md', 'lg'],
        },
        {
            pattern: /hidden/,
            variants: ['sm', 'md', 'lg'],
        },
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            zIndex: {
                75: 75,
            },
            colors: {
                danger: colors.rose,
                primary: colors.blue,
                success: colors.green,
                warning: colors.yellow,
            },
        },
    },
    plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
};
