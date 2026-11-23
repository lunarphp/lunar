import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { createApp, defineComponent, h, nextTick, type App as VueApp, type DefineComponent } from 'vue';
import { config, http as inertiaHttp, router, type HttpResponse, type Page } from '@inertiajs/core';
import { App } from '@inertiajs/vue3';
import { config as testConfig } from '@vue/test-utils';
import { useEditDraft } from '../composables/useEditDraft';
import { panelVisitOptions } from './visitOptions';

const { draftHttp } = vi.hoisted(() => ({
    draftHttp: { patch: vi.fn(), post: vi.fn(), delete: vi.fn() },
}));

vi.mock('../lib/http', async (importOriginal) => ({
    ...(await importOriginal<typeof import('../lib/http')>()),
    http: draftHttp,
}));

function editPage(id: number, name: string): Page {
    return {
        component: 'products/Edit',
        url: `/panel/products/${id}/edit`,
        version: null,
        props: {
            errors: {},
            product: { id, name },
            urls: { draft: `/panel/products/${id}/draft`, commit: `/panel/products/${id}/draft/commit` },
        },
    } as unknown as Page;
}

// Stands in for pages/products/Edit.vue: builds its draft form once in setup
// from the props it mounted with.
const ProductEdit = defineComponent({
    props: {
        product: { type: Object as () => { id: number; name: string }, required: true },
        urls: { type: Object as () => { draft: string; commit: string }, required: true },
        errors: { type: Object, default: () => ({}) },
    },
    setup(props) {
        const form = useEditDraft({ initial: { name: props.product.name }, draft: null, urls: props.urls, slices: false });

        return () => h('div', [h('span', { id: 'name' }, String(form.values.name)), h('button', { id: 'save', onClick: () => form.commit() })]);
    },
}) as unknown as DefineComponent;

describe('panelVisitOptions', () => {
    let app: VueApp | null = null;
    let nextResponse: Page;

    beforeEach(async () => {
        draftHttp.post.mockResolvedValue({ committed: true, version: 0 });
        window.history.replaceState({}, '', '/panel/products/11/edit');

        inertiaHttp.setClient({
            request: async (): Promise<HttpResponse> => ({
                status: 200,
                data: JSON.stringify(nextResponse),
                headers: { 'x-inertia': 'true', 'content-type': 'application/json' },
            }),
        });

        const el = document.createElement('div');
        document.body.appendChild(el);

        // What createInertiaApp({ defaults }) in app.ts does.
        config.replace({ visitOptions: panelVisitOptions });

        app = createApp({
            render: () =>
                h(App, {
                    initialPage: editPage(11, 'Original'),
                    initialComponent: ProductEdit,
                    resolveComponent: () => ProductEdit,
                    serverRendered: false,
                }),
        });
        // The i18n plugin test-setup.ts installs for mount().
        testConfig.global.plugins.forEach((installed) => app?.use(installed as Parameters<VueApp['use']>[0]));
        app.mount(el);
        await nextTick();
    });

    afterEach(() => {
        app?.unmount();
        app = null;
        config.replace({});
        vi.clearAllMocks();
    });

    async function post(url: string): Promise<void> {
        await new Promise<void>((resolve) => router.post(url, {}, { onFinish: () => resolve() }));
        await nextTick();
    }

    it('rebuilds the draft form when a post lands on another record of the same page', async () => {
        nextResponse = editPage(18, 'Original (copy)');

        await post('/panel/products/11/duplicate');

        expect(window.location.pathname).toBe('/panel/products/18/edit');
        expect(document.querySelector('#name')?.textContent).toBe('Original (copy)');

        (document.querySelector('#save') as HTMLButtonElement).click();
        await nextTick();

        expect(draftHttp.post).toHaveBeenCalledWith('/panel/products/18/draft/commit', expect.anything());
    });

    it('keeps component state when a post stays on the current page', async () => {
        nextResponse = editPage(11, 'Renamed on the server');

        await post('/panel/products/11/options/generate');

        // Same instance: the form keeps the values it was built with.
        expect(document.querySelector('#name')?.textContent).toBe('Original');
    });

    it('honours an explicit preserveState: false and the errors option', () => {
        window.history.replaceState({}, '', '/panel/products/11/edit');
        const page = editPage(11, 'x');

        expect(panelVisitOptions('/x', { preserveState: false })).toEqual({});

        const errorsOnly = panelVisitOptions('/x', { preserveState: 'errors' }).preserveState as (page: Page) => boolean;
        expect(errorsOnly(page)).toBe(false);
        expect(errorsOnly({ ...page, props: { ...page.props, errors: { name: 'Required' } } } as unknown as Page)).toBe(true);
    });
});
