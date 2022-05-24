module.exports = {
    darkMode: 'class',
    content: [
        './resources/views/components/**/*.blade.php',
        './resources/views/field-types/**/*.blade.php',
        './resources/views/layouts/**/*.blade.php',
        './resources/views/livewire/**/*.blade.php',
        './resources/views/partials/**/*.blade.php',
        './resources/assets/**/*.js',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/**/*.blade.php',
        './vendor/livewire-ui/modal/resources/views/**/*.blade.php',
    ],
    theme: {
        fontFamily: {
            sans: ['Nunito', 'ui-sans-serif', 'system-ui'],
            serif: ['ui-serif', 'Georgia'],
            mono: ['ui-monospace', 'SFMono-Regular'],
        },
        extend: {
            zIndex: {
                75: 75,
            },
            cursor: {
                grab: 'grab',
            },
        },
    },
    variants: {
        extend: {
            opacity: ['disabled'],
            cursor: ['disabled'],
            backgroundColor: ['even'],
        },
    },
    plugins: [
        require('@tailwindcss/forms')({
            strategy: 'class',
        }),
    ],
};
