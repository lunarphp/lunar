import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h, type DefineComponent } from 'vue';
import * as Vue from 'vue';
import '../css/app.css';
import { LunarPanelRuntime } from './runtime/registry';
import { createPageResolver } from './runtime/pageResolver';
import { whenDomContentLoaded } from './runtime/domReady';
import { useTheme } from './composables/useTheme';
import { bootTranslations, createPanelI18n } from './i18n';
import * as LunarPanelUI from './ui';
import PanelLayout from './layouts/PanelLayout.vue';

// Published so add-on IIFE bundles (compiled against `@lunarphp/panel-vite-plugin`,
// which externalises `vue` and `@lunarphp/panel`) can resolve Vue and the panel's
// own layout/page components without bundling copies.
window.Vue = Vue;
window.LunarPanelUI = LunarPanelUI;
window.LunarPanel = new LunarPanelRuntime();

// The shell layout auto-applied to add-on pages (see pageResolver); add-on pages
// get the sidebar chrome without importing or wrapping it.
window.LunarPanel.registerLayout('default', PanelLayout);

const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue', { eager: true });

// `window.LunarPanel`'s declared type is the public add-on-facing interface (see
// lunar-panel.d.ts); the resolver needs the internal getPage/markBooted surface the
// concrete runtime actually exposes.
createInertiaApp({
    resolve: createPageResolver(window.LunarPanel as LunarPanelRuntime, pages),
    async setup({ el, App, props, plugin }) {
        // Applies the persisted/system theme class before first paint.
        useTheme();

        const locale = (props.initialPage.props.locale as string | undefined) ?? 'en';
        const panelPath = (props.initialPage.props.panel as { path: string } | undefined)?.path ?? 'panel';

        const i18n = createPanelI18n(locale);

        // Awaited only on a cold localStorage cache (first-ever visit); a
        // cached hit resolves synchronously so this never delays subsequent
        // page loads. See i18n.ts for the full cache/fallback tradeoffs.
        await bootTranslations(i18n, window.LunarPanel as LunarPanelRuntime, panelPath, locale);

        // Hold the first render until add-on IIFEs have registered their slot
        // components and table extensions; registration is not reactive, so a
        // first-party page mounted before an add-on script ran would render its
        // slot zones empty and never recover. See runtime/domReady.ts.
        await whenDomContentLoaded();

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            .mount(el);

        (window.LunarPanel as LunarPanelRuntime).markBooted();
    },
});
