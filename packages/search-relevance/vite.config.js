import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import lunarPanelPlugin from '@lunarphp/panel-vite-plugin';

// Compiles resources/js/addon.ts to one IIFE bundle sharing the panel's Vue instance.
export default defineConfig({
    plugins: [
        vue(),
        lunarPanelPlugin({ name: 'LunarSearchRelevanceAddon' }),
    ],
    build: {
        outDir: 'build',
        rollupOptions: {
            input: 'resources/js/addon.ts',
        },
    },
});
