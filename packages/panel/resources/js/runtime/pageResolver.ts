import type { DefineComponent } from 'vue';
import type { LunarPanelRuntime } from './registry';
import { whenDomContentLoaded } from './domReady';

/**
 * Builds the Inertia `resolve` callback: the runtime registry (populated by add-on
 * IIFEs) is checked before local pages. On a hard-refresh-style miss, deferred add-on
 * <script> IIFEs (see app.blade.php) may not have registered their pages yet, so this
 * waits for `DOMContentLoaded` and retries both sources before giving up.
 */
export function createPageResolver(
    runtime: LunarPanelRuntime,
    localPages: Record<string, DefineComponent>,
): (name: string) => Promise<DefineComponent> {
    function resolveAnyPage(name: string): DefineComponent | undefined {
        const addonPage = runtime.getPage(name) as (DefineComponent & { layout?: unknown }) | undefined;

        if (addonPage) {
            // Auto-apply the shell layout so add-on pages get the sidebar chrome
            // without wrapping it. First-party pages wrap their own layout in the
            // template, so this only touches add-on pages. `??=` leaves an add-on
            // that set its own persistent layout alone. v3 tightened `layout`'s
            // type from a bare `Component` to `LayoutCallbackReturn`, so narrow the
            // registry's `Component | undefined` return once it is known present.
            const defaultLayout = runtime.getLayout('default');

            if (defaultLayout) {
                addonPage.layout ??= defaultLayout as DefineComponent;
            }

            return addonPage;
        }

        return localPages[`./pages/${name}.vue`];
    }

    return async (name: string): Promise<DefineComponent> => {
        const page = resolveAnyPage(name);

        if (page) {
            return page;
        }

        await whenDomContentLoaded();

        const retried = resolveAnyPage(name);

        if (retried) {
            return retried;
        }

        throw new Error(`Panel page not found: ${name}`);
    };
}
