import type { DefineComponent } from 'vue';
import type { LunarPanelRuntime } from './registry';

function waitForDomContentLoaded(): Promise<void> {
    return new Promise((resolve) => {
        document.addEventListener('DOMContentLoaded', () => resolve(), { once: true });
    });
}

/**
 * Builds the Inertia `resolve` callback: the runtime registry (populated by add-on
 * IIFEs) is checked before local pages. On a hard-refresh-style miss, deferred add-on
 * <script> IIFEs (see app.blade.php) may not have registered their pages yet, so this
 * waits once for `DOMContentLoaded` and retries both sources before giving up.
 */
export function createPageResolver(
    runtime: LunarPanelRuntime,
    localPages: Record<string, DefineComponent>,
): (name: string) => Promise<DefineComponent> {
    function resolveAnyPage(name: string): DefineComponent | undefined {
        return (runtime.getPage(name) as DefineComponent | undefined) ?? localPages[`./pages/${name}.vue`];
    }

    return async (name: string): Promise<DefineComponent> => {
        const page = resolveAnyPage(name);

        if (page) {
            return page;
        }

        if (document.readyState !== 'complete') {
            await waitForDomContentLoaded();

            const retried = resolveAnyPage(name);

            if (retried) {
                return retried;
            }
        }

        throw new Error(`Panel page not found: ${name}`);
    };
}
