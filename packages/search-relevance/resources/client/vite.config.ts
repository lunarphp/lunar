import { defineConfig } from 'vitest/config';

// ESM library build: the framework-agnostic entry and the Vue layer, with
// vue left external so the storefront's own copy is used.
export default defineConfig({
    build: {
        outDir: 'dist',
        emptyOutDir: false,
        lib: {
            entry: { index: 'src/index.ts', vue: 'src/vue.ts' },
            formats: ['es'],
        },
        rollupOptions: { external: ['vue'] },
    },
    test: {
        environment: 'happy-dom',
        globals: true,
    },
});
