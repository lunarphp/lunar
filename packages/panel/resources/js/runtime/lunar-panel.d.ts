import type { Component } from 'vue';

/**
 * Public type surface for `window.LunarPanel`, published for add-on authors.
 * Mirrors `LunarPanelRuntime` in `registry.ts` — keep in sync.
 */
export interface LunarPanelRuntime {
    /** Runs `cb` immediately if the panel has already booted, otherwise queues it. */
    booting(cb: () => void): void;
    registerPages(pages: Record<string, Component>): void;
    registerComponents(namespace: string, components: Record<string, Component>): void;
    resolveExtensionComponent(name: string): Component | undefined;
    registerLayout(name: string, layout: Component): void;
    registerTranslations(locale: string, namespace: string, messages: Record<string, string>): void;
}

declare global {
    interface Window {
        LunarPanel: LunarPanelRuntime;
        Vue: typeof import('vue');
        InertiaVue3: typeof import('@inertiajs/vue3');
        VueI18n: typeof import('vue-i18n');
        /**
         * Panel components exposed to add-on bundles. Derived from the ui.ts barrel
         * (the source of truth) so it never drifts from what is actually published.
         */
        LunarPanelUI: typeof import('../ui');
    }
}

export {};
