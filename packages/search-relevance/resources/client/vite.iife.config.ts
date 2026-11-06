import { defineConfig } from 'vite';

// Self-contained script exposing window.LunarSearchRelevance. The Blade
// tracking component inlines this file so Blade and headless storefronts
// share one implementation. Unminified: it is inlined into page markup.
export default defineConfig({
    build: {
        outDir: 'dist',
        emptyOutDir: false,
        minify: false,
        lib: {
            entry: 'src/index.ts',
            name: 'LunarSearchRelevance',
            formats: ['iife'],
            fileName: () => 'tracking.iife.js',
        },
    },
});
