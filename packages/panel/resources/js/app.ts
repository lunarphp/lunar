import { createInertiaApp } from '@inertiajs/vue3';
import * as InertiaVue3 from '@inertiajs/vue3';
import { createApp, h, type DefineComponent } from 'vue';
import * as Vue from 'vue';
import * as VueI18n from 'vue-i18n';
import '../css/app.css';
import { LunarPanelRuntime } from './runtime/registry';
import { createPageResolver } from './runtime/pageResolver';
import { whenDomContentLoaded } from './runtime/domReady';
import { useTheme } from './composables/useTheme';
import { bootTranslations, createPanelI18n } from './i18n';
import * as LunarPanelUI from './ui';
import PanelLayout from './layouts/PanelLayout.vue';

// Published so add-on IIFE bundles (compiled against `@lunarphp/panel-vite-plugin`,
// which externalises `vue`, `@inertiajs/vue3`, `vue-i18n` and `@lunarphp/panel`) can
// resolve Vue, Inertia, i18n and the panel's own layout/page components without
// bundling copies. Sharing one module instance is what makes `usePage()`/`<Link>`
// and `useI18n()` work in add-on pages: the composables read module-level state
// this app instance owns.
window.Vue = Vue;
window.InertiaVue3 = InertiaVue3;
window.VueI18n = VueI18n;
window.LunarPanelUI = LunarPanelUI;
window.LunarPanel = new LunarPanelRuntime();

// The shell layout auto-applied to add-on pages (see pageResolver); add-on pages
// get the sidebar chrome without importing or wrapping it.
window.LunarPanel.registerLayout('default', PanelLayout);

const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue', { eager: true });

// Browser-tab title suffix; assigned from the shared panel prop in setup(),
// which runs before the head manager (installed by the plugin during mount)
// resolves any page title.
let panelName = 'Lunar';

// `window.LunarPanel`'s declared type is the public add-on-facing interface (see
// lunar-panel.d.ts); the resolver needs the internal getPage/markBooted surface the
// concrete runtime actually exposes.
createInertiaApp({
    // Pages provide their title through <Head> (rendered by the shared
    // scaffold); pages without one fall back to the bare panel name.
    title: (title) => (title ? `${title} — ${panelName}` : panelName),
    resolve: createPageResolver(window.LunarPanel as LunarPanelRuntime, pages),
    async setup({ el, App, props, plugin }) {
        // Applies the persisted/system theme class before first paint.
        useTheme();

        const locale = (props.initialPage.props.locale as string | undefined) ?? 'en';
        const panel = props.initialPage.props.panel as { path?: string; name?: string } | undefined;
        const panelPath = panel?.path ?? 'panel';
        panelName = panel?.name ?? 'Lunar';

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
