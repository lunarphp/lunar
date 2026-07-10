import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h, type DefineComponent } from 'vue';
import * as Vue from 'vue';
import '../css/app.css';
import { LunarPanelRuntime } from './runtime/registry';

// Published so add-on IIFE bundles (compiled against `@lunarphp/panel/vite-plugin`,
// which externalises `vue`) can resolve Vue without bundling their own copy.
window.Vue = Vue;
window.LunarPanel = new LunarPanelRuntime();

const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue', { eager: true });

function resolveLocalPage(name: string): DefineComponent | undefined {
    return pages[`./pages/${name}.vue`];
}

function resolveAnyPage(name: string): DefineComponent | undefined {
    return (window.LunarPanel.getPage(name) as DefineComponent | undefined) ?? resolveLocalPage(name);
}

function waitForDomContentLoaded(): Promise<void> {
    return new Promise((resolve) => {
        document.addEventListener('DOMContentLoaded', () => resolve(), { once: true });
    });
}

createInertiaApp({
    resolve: async (name) => {
        const page = resolveAnyPage(name);

        if (page) {
            return page;
        }

        // On a hard refresh, deferred add-on <script> IIFEs (see app.blade.php) may not
        // have registered their pages yet. Wait once for the document to finish loading,
        // then retry, before giving up.
        if (document.readyState !== 'complete') {
            await waitForDomContentLoaded();

            const retried = resolveAnyPage(name);

            if (retried) {
                return retried;
            }
        }

        throw new Error(`Panel page not found: ${name}`);
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);

        window.LunarPanel.markBooted();
    },
});
