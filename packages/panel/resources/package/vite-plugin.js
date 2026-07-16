import { existsSync, renameSync, rmSync } from 'node:fs';
import { join } from 'node:path';

/**
 * Vite plugin for add-on packages that extend the Lunar panel.
 *
 * Forces IIFE output and externalises `vue` to the `window.Vue` global the panel's own
 * `app.ts` publishes at startup, so add-on bundles share the panel's Vue instance instead
 * of bundling their own — required for `window.LunarPanel` component registration to work.
 *
 * Also externalises `@lunarphp/panel` to the `window.LunarPanelUI` global, so add-on pages
 * import the panel's own layout and page-chrome components (PageHeader, PageZone, Button, …)
 * instead of bundling copies.
 *
 * Also externalises `@inertiajs/vue3` to the `window.InertiaVue3` global. Inertia's
 * composables (`usePage`, `router`, `<Link>`) read module-level state owned by the app
 * instance that called `createInertiaApp` — sharing the panel's module instance is what
 * makes them work inside add-on pages; a bundled second copy would read uninitialised
 * state.
 *
 * Also externalises `vue-i18n` to the `window.VueI18n` global for the same reason:
 * `useI18n()` must resolve the i18n instance the panel installed (which holds the
 * messages served by the translations endpoint, including `{namespace}::{group}`
 * add-on groups); a bundled copy would see no instance and no messages.
 *
 * Also enables Vite's manifest and moves it from Vite's default `.vite/manifest.json`
 * to the build root as `manifest.json`, where Laravel's Vite resolves it (it only reads
 * `{buildDirectory}/manifest.json`). This lets the add-on's `build/` be published as-is
 * and the panel emit its script tag via `PanelManager::vite()`.
 *
 * @param {{ name?: string }} [options]
 */
export default function lunarPanelPlugin(options = {}) {
    const globalName = options.name ?? 'LunarPanelAddon';

    return {
        name: 'lunar-panel',
        config(config) {
            return {
                ...config,
                build: {
                    ...config.build,
                    manifest: true,
                    rollupOptions: {
                        ...config.build?.rollupOptions,
                        external: ['vue', '@inertiajs/vue3', 'vue-i18n', '@lunarphp/panel', ...(config.build?.rollupOptions?.external ?? [])],
                        output: {
                            ...config.build?.rollupOptions?.output,
                            format: 'iife',
                            name: globalName,
                            globals: {
                                'vue': 'Vue',
                                '@inertiajs/vue3': 'InertiaVue3',
                                'vue-i18n': 'VueI18n',
                                '@lunarphp/panel': 'LunarPanelUI',
                                ...config.build?.rollupOptions?.output?.globals,
                            },
                        },
                    },
                },
            };
        },
        writeBundle(outputOptions) {
            const dir = outputOptions.dir;
            if (!dir) {
                return;
            }

            const viteManifest = join(dir, '.vite', 'manifest.json');
            if (existsSync(viteManifest)) {
                renameSync(viteManifest, join(dir, 'manifest.json'));
                rmSync(join(dir, '.vite'), { recursive: true, force: true });
            }
        },
    };
}
