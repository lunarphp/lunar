import { fileURLToPath } from 'node:url';
import vue from '@vitejs/plugin-vue';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            // Add-on pages (table-rate-shipping) import the panel as
            // @lunarphp/panel; tests that mount them get the panel source.
            '@lunarphp/panel': fileURLToPath(new URL('./resources/js/ui.ts', import.meta.url)),
        },
        // Add-on pages resolve these from the workspace root; one copy each.
        dedupe: ['vue', '@inertiajs/vue3', 'vue-i18n'],
    },
    test: {
        environment: 'happy-dom',
        setupFiles: ['resources/js/test-setup.ts'],
    },
});
