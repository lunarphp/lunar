import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import lunarPanelPlugin from '@lunarphp/panel-vite-plugin';

// Compiles resources/js/addon.ts to a single IIFE bundle that shares the
// panel's Vue instance (window.Vue) instead of bundling its own copy.
export default defineConfig({
    plugins: [
        vue(),
        tailwindcss(),
        lunarPanelPlugin({ name: 'LunarPanelExampleAddon' }),
    ],
    build: {
        outDir: 'build',
        // Writes the add-on's CSS to its own style.css instead of injecting
        // it from the script, so it loads as a stylesheet after the panel's.
        cssCodeSplit: false,
        rollupOptions: {
            input: 'resources/js/addon.ts',
        },
    },
});
