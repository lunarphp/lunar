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
 * Also enables Vite's manifest and moves it from Vite's default `.vite/manifest.json`
 * to the build root as `manifest.json`, where Laravel's Vite resolves it (it only reads
 * `{buildDirectory}/manifest.json`). This lets the add-on's `build/` be published as-is
 * and the panel emit its script tag via `PanelManager::vite()`.
 *
 * `@inertiajs/vue3` is NOT externalised: the panel does not currently publish it as a
 * window global, so add-ons needing Inertia composables (`usePage`, `Link`, ...) bundle
 * their own copy for now. Follow-up: publish a `window.InertiaVue3` global and externalise
 * it here too, once cross-bundle `usePage()` provide/inject compatibility is verified.
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
                        external: ['vue', '@lunarphp/panel', ...(config.build?.rollupOptions?.external ?? [])],
                        output: {
                            ...config.build?.rollupOptions?.output,
                            format: 'iife',
                            name: globalName,
                            globals: {
                                'vue': 'Vue',
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
