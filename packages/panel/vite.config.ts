import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

// The panel's built assets are served from public/vendor/lunar-panel/build
// (symlinked from public/build; see PanelServiceProvider + `lunar:panel:link`),
// but buildDirectory 'build' would bake a `/build/` base into asset urls() --
// so bundled CSS assets like flag-icons SVGs 404. Pin the build-time base to the
// real serve path so every bundled url() resolves; dev keeps the plugin default.
export default defineConfig(({ command }) => ({
    base: command === 'build' ? '/vendor/lunar-panel/build/' : undefined,
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
}));
