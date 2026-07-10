import { afterEach, describe, expect, it } from 'vitest';
import { defineComponent, type DefineComponent } from 'vue';
import { LunarPanelRuntime } from './registry';
import { createPageResolver } from './pageResolver';

function setReadyState(value: DocumentReadyState): void {
    Object.defineProperty(document, 'readyState', { value, configurable: true });
}

// `DefineComponent`'s bare (ungenerified) form doesn't structurally unify with the
// heavily-generic type `defineComponent()` infers, so test doubles are cast explicitly.
function testPage(): DefineComponent {
    return defineComponent({}) as unknown as DefineComponent;
}

describe('createPageResolver', () => {
    afterEach(() => {
        setReadyState('complete');
    });

    it('resolves from the runtime registry when present, without needing local pages', async () => {
        const runtime = new LunarPanelRuntime();
        const page = testPage();
        runtime.registerPages({ Dashboard: page });

        const resolve = createPageResolver(runtime, {});

        await expect(resolve('Dashboard')).resolves.toBe(page);
    });

    it('falls back to local pages when not in the registry', async () => {
        const runtime = new LunarPanelRuntime();
        const page = testPage();
        const localPages = { './pages/Dashboard.vue': page };

        const resolve = createPageResolver(runtime, localPages);

        await expect(resolve('Dashboard')).resolves.toBe(page);
    });

    it('waits for DOMContentLoaded then retries, resolving if the page registers in that window', async () => {
        setReadyState('loading');
        const runtime = new LunarPanelRuntime();
        const resolve = createPageResolver(runtime, {});

        const pending = resolve('Dashboard');

        const page = testPage();
        runtime.registerPages({ Dashboard: page });
        document.dispatchEvent(new Event('DOMContentLoaded'));

        await expect(pending).resolves.toBe(page);
    });

    it('throws a clear "Panel page not found" error if still unresolved after the retry', async () => {
        setReadyState('loading');
        const runtime = new LunarPanelRuntime();
        const resolve = createPageResolver(runtime, {});

        const pending = resolve('Missing');
        document.dispatchEvent(new Event('DOMContentLoaded'));

        await expect(pending).rejects.toThrow('Panel page not found: Missing');
    });
});
