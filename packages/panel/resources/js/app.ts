import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h, type DefineComponent } from 'vue';
import * as Vue from 'vue';
import '../css/app.css';
import { LunarPanelRuntime } from './runtime/registry';
import { createPageResolver } from './runtime/pageResolver';
import { useTheme } from './composables/useTheme';
import { bootTranslations, createPanelI18n } from './i18n';

// Published so add-on IIFE bundles (compiled against `@lunarphp/panel/vite-plugin`,
// which externalises `vue`) can resolve Vue without bundling their own copy.
window.Vue = Vue;
window.LunarPanel = new LunarPanelRuntime();

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

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            .mount(el);

        window.LunarPanel.markBooted();
    },
});
