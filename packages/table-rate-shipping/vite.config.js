import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import lunarPanelPlugin from '@lunarphp/panel-vite-plugin';

// Compiles resources/js/panel.ts to a single IIFE bundle that shares the
// panel's Vue, Inertia, vue-i18n and component runtime through window globals.
// The output in build/ is committed: the package installs through Composer, so
// the split repository has to carry the compiled assets.
export default defineConfig({
    plugins: [
        vue(),
        lunarPanelPlugin({ name: 'LunarShippingPanel' }),
    ],
    build: {
        outDir: 'build',
        rollupOptions: {
            input: 'resources/js/panel.ts',
        },
    },
});
