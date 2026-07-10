/**
 * Vite plugin for add-on packages that extend the Lunar panel.
 *
 * Forces IIFE output and externalises `vue` to the `window.Vue` global the panel's own
 * `app.ts` publishes at startup, so add-on bundles share the panel's Vue instance instead
 * of bundling their own — required for `window.LunarPanel` component registration to work.
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
                    rollupOptions: {
                        ...config.build?.rollupOptions,
                        external: ['vue', ...(config.build?.rollupOptions?.external ?? [])],
                        output: {
                            ...config.build?.rollupOptions?.output,
                            format: 'iife',
                            name: globalName,
                            globals: {
                                vue: 'Vue',
                                ...config.build?.rollupOptions?.output?.globals,
                            },
                        },
                    },
                },
            };
        },
    };
}
