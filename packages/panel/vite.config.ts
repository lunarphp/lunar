import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            publicDirectory: 'public',
            buildDirectory: 'build',
            hotFile: 'public/build/hot',
        }),
        vue(),
        tailwindcss(),
    ],
});
