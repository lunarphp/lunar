import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { createApp, defineComponent, h, nextTick, type App as VueApp, type DefineComponent } from 'vue';
import { config, http as inertiaHttp, router, type HttpRequestConfig, type HttpResponse, type Page } from '@inertiajs/core';
import { App } from '@inertiajs/vue3';
import { config as testConfig } from '@vue/test-utils';
import { panelVisitOptions } from '../../runtime/visitOptions';

// The shell, overlays and pickers are stubbed down to their slots and
// events; everything else (the page, its form, Inertia's router and the
// panel's visit defaults) is real.
vi.mock('../../layouts/SettingsShell.vue', async () => {
    const { defineComponent, h } = await import('vue');

    return {
        default: defineComponent({
            setup: (_props, { slots }) => () => h('div', [slots.actions?.(), slots.default?.()]),
        }),
    };
});

vi.mock('../../components/Tooltip.vue', async () => {
    const { defineComponent } = await import('vue');

    return { default: defineComponent({ setup: (_props, { slots }) => () => slots.default?.() }) };
});

vi.mock('../../components/Slideout.vue', async () => {
    const { defineComponent, h } = await import('vue');

    return {
        default: defineComponent({
            props: { open: Boolean },
            setup: (props, { slots }) => () => (props.open ? h('div', { class: 'slideout' }, [slots.default?.(), slots.footer?.()]) : null),
        }),
    };
});

// One button per option; clicking it picks the option.
vi.mock('../../components/Combobox.vue', async () => {
    const { defineComponent, h } = await import('vue');

    return {
        default: defineComponent({
            props: { options: { type: Array, default: () => [] }, modelValue: { type: [String, Number], default: null } },
            emits: ['change', 'update:modelValue'],
            setup: (props, { emit }) => () =>
                h('div', (props.options as { value: number; label: string }[]).map((option) =>
                    h('button', {
                        type: 'button',
                        'data-option': option.label,
                        onClick: () => {
                            emit('update:modelValue', option.value);
                            emit('change', option.value);
                        },
                    }, option.label))),
        }),
    };
});

// Picks one fixed product while open.
vi.mock('../../components/TargetPickerDialog.vue', async () => {
    const { defineComponent, h } = await import('vue');

    return {
        default: defineComponent({
            props: { open: Boolean },
            emits: ['add', 'update:open'],
            setup: (props, { emit }) => () =>
                (props.open
                    ? h('button', { type: 'button', 'data-pick': 'mug', onClick: () => emit('add', [{ kind: 'products', id: 7, label: 'Mug', hint: null }]) }, 'pick')
                    : null),
        }),
    };
});

import ChannelEdit from './channels/Edit.vue';
import ZoneEdit from '../../../../../table-rate-shipping/resources/js/pages/settings/shipping/zones/Edit.vue';
import ExclusionListEdit from '../../../../../table-rate-shipping/resources/js/pages/settings/shipping/exclusion-lists/Edit.vue';

const Index = defineComponent({ setup: () => () => h('div', { id: 'index' }, 'index') });

const ZONE_URL = '/panel/settings/shipping/zones/1/edit';
const UNITED_KINGDOM = 235;

function zonePage(countries: number[], rates: unknown[] = [], errors: Record<string, string> = {}): Page {
    return {
        component: 'shipping::settings/shipping/zones/Edit',
        url: ZONE_URL,
        version: null,
        props: {
            errors,
            zone: { id: 1, name: 'Domestic', type: 'countries' },
            coverage: { countries, states: [], postcodes: [] },
            exclusionLists: [],
            rates,
            methods: [{ id: 3, name: 'Standard', charge_by: 'cart_total', weight_unit: 'kg' }],
            currencies: [{ id: 1, code: 'GBP', name: 'Pound', decimal_places: 2, default: true }],
            customerGroups: [],
            allExclusionLists: [],
            countries: [{ id: UNITED_KINGDOM, name: 'United Kingdom', iso2: 'GB' }],
            states: [],
            pricesIncludeTax: false,
            urls: { update: '/panel/settings/shipping/zones/1', destroy: '/panel/settings/shipping/zones/1', index: '/panel/settings/shipping/zones', ratesStore: '/panel/settings/shipping/zones/1/rates' },
        },
    } as unknown as Page;
}

function channelPage(name: string, errors: Record<string, string> = {}): Page {
    return {
        component: 'settings/channels/Edit',
        url: '/panel/settings/channels/1/edit',
        version: null,
        props: {
            errors,
            channel: { id: 1, name, handle: 'webstore', url: null, default: true, status: 'active' },
            hasOrderHistory: false,
            urls: { update: '/panel/settings/channels/1', destroy: '/panel/settings/channels/1', index: '/panel/settings/channels' },
        },
    } as unknown as Page;
}

function exclusionListPage(products: unknown[]): Page {
    return {
        component: 'shipping::settings/shipping/exclusion-lists/Edit',
        url: '/panel/settings/shipping/exclusion-lists/2/edit',
        version: null,
        props: {
            errors: {},
            list: { id: 2, name: 'Fragile', zones: [] },
            products,
            urls: { update: '/panel/settings/shipping/exclusion-lists/2', destroy: '/panel/settings/shipping/exclusion-lists/2', index: '/panel/settings/shipping/exclusion-lists', search: '/panel/search' },
        },
    } as unknown as Page;
}

const indexPage = (url: string): Page => ({ component: 'Index', url, version: null, props: { errors: {} } }) as unknown as Page;

const components = {
    'settings/channels/Edit': ChannelEdit,
    'shipping::settings/shipping/zones/Edit': ZoneEdit,
    'shipping::settings/shipping/exclusion-lists/Edit': ExclusionListEdit,
    Index,
} as unknown as Record<string, DefineComponent>;

describe('settings edit pages leave guard', () => {
    let app: VueApp | null = null;
    let respond: (request: HttpRequestConfig) => Page;
    let requests: HttpRequestConfig[];
    let confirmMock: ReturnType<typeof vi.fn>;

    const settle = async (): Promise<void> => {
        for (let i = 0; i < 10; i++) {
            await new Promise((resolve) => setTimeout(resolve, 0));
            await nextTick();
        }
    };

    const click = async (selector: string): Promise<void> => {
        const element = document.querySelector<HTMLElement>(selector);

        expect(element, selector).not.toBeNull();
        element!.click();
        await settle();
    };

    const clickButton = async (label: string, within: ParentNode = document): Promise<void> => {
        const button = [...within.querySelectorAll('button')].find((candidate) => candidate.textContent?.trim() === label);

        expect(button, label).toBeDefined();
        button!.click();
        await settle();
    };

    const mountPage = async (page: Page): Promise<void> => {
        window.history.replaceState({}, '', page.url);

        const el = document.createElement('div');
        document.body.appendChild(el);

        app = createApp({
            render: () =>
                h(App, {
                    initialPage: page,
                    initialComponent: components[page.component],
                    resolveComponent: (name: string) => components[name],
                    serverRendered: false,
                }),
        });
        testConfig.global.plugins.forEach((installed) => app?.use(installed as Parameters<VueApp['use']>[0]));
        app.mount(el);
        await settle();
    };

    const leave = async (url: string): Promise<void> => {
        router.visit(url);
        await settle();
    };

    const beforeUnloadPrevented = (): boolean => {
        const event = new Event('beforeunload', { cancelable: true });
        window.dispatchEvent(event);

        return event.defaultPrevented;
    };

    const chipLabels = (): string[] => [...document.querySelectorAll('[data-screen-label] span')].map((chip) => chip.textContent?.trim() ?? '');

    beforeEach(() => {
        requests = [];
        confirmMock = vi.fn().mockReturnValue(false);
        vi.stubGlobal('confirm', confirmMock);

        inertiaHttp.setClient({
            request: async (request: HttpRequestConfig): Promise<HttpResponse> => {
                requests.push(request);

                return {
                    status: 200,
                    data: JSON.stringify(respond(request)),
                    headers: { 'x-inertia': 'true', 'content-type': 'application/json' },
                };
            },
        });

        // What createInertiaApp({ defaults }) in app.ts does.
        config.replace({ visitOptions: panelVisitOptions });
    });

    afterEach(() => {
        app?.unmount();
        app = null;
        document.body.innerHTML = '';
        config.replace({});
        vi.unstubAllGlobals();
    });

    describe('a page-level settings form', () => {
        it('prompts before an Inertia visit away while dirty, and stays on decline', async () => {
            respond = (request) => indexPage(new URL(request.url, window.location.origin).pathname);
            await mountPage(channelPage('Webstore'));

            const input = document.querySelector<HTMLInputElement>('input:not([disabled])')!;
            input.value = 'Webstore EU';
            input.dispatchEvent(new Event('input'));
            await settle();

            await leave('/panel/settings/channels');

            expect(confirmMock).toHaveBeenCalledWith('common.leave_unsaved_confirm');
            expect(window.location.pathname).toBe('/panel/settings/channels/1/edit');
            expect(document.querySelector('#index')).toBeNull();
            expect(beforeUnloadPrevented()).toBe(true);
        });

        it('does not prompt on a validation-error redirect, and still guards afterwards', async () => {
            respond = () => channelPage('Webstore', { name: 'The name field is required.' });
            await mountPage(channelPage('Webstore'));

            const input = document.querySelector<HTMLInputElement>('input:not([disabled])')!;
            input.value = '';
            input.dispatchEvent(new Event('input'));
            await settle();

            await clickButton('common.save');

            expect(requests.map((request) => request.method)).toEqual(['put']);
            expect(confirmMock).not.toHaveBeenCalled();
            expect(document.body.textContent).toContain('The name field is required.');

            respond = (request) => indexPage(new URL(request.url, window.location.origin).pathname);
            await leave('/panel/settings/channels');

            expect(confirmMock).toHaveBeenCalledTimes(1);
        });

        it('does not prompt after a successful save', async () => {
            respond = () => channelPage('Webstore EU');
            await mountPage(channelPage('Webstore'));

            const input = document.querySelector<HTMLInputElement>('input:not([disabled])')!;
            input.value = 'Webstore EU';
            input.dispatchEvent(new Event('input'));
            await settle();

            await clickButton('common.save');

            respond = (request) => indexPage(new URL(request.url, window.location.origin).pathname);
            await leave('/panel/settings/channels');

            expect(confirmMock).not.toHaveBeenCalled();
            expect(beforeUnloadPrevented()).toBe(false);
            expect(window.location.pathname).toBe('/panel/settings/channels');
        });
    });

    describe('shipping zone', () => {
        const rate = {
            id: 9,
            shipping_method_id: 3,
            method_name: 'Standard',
            enabled: true,
            base_price: 'GBP 5.00',
            tiers_count: 0,
            base_prices: { GBP: 5 },
            tiers: [],
            urls: { update: '/panel/settings/shipping/rates/9', destroy: '/panel/settings/shipping/rates/9' },
        };

        it('keeps an unsaved country through a rate save and guards it until the zone is saved', async () => {
            await mountPage(zonePage([]));

            await click(`[data-option="United Kingdom"]`);
            expect(chipLabels()).toContain('United Kingdom');

            // Add rate, then Save in the rate dialog: its own visit, which the
            // server answers with the zone page. The zone's countries are
            // still the stored (empty) set.
            respond = () => zonePage([], [rate]);
            await clickButton('shipping::zones.add_rate');
            await clickButton('common.save', document.querySelector('.slideout')!);

            expect(requests.map((request) => [request.method, new URL(request.url, window.location.origin).pathname])).toEqual([
                ['post', '/panel/settings/shipping/zones/1/rates'],
            ]);
            expect(confirmMock).not.toHaveBeenCalled();

            // The page form is preserved: the unsaved country is still on the
            // page, the new rate is listed, and leaving warns about it.
            expect(chipLabels()).toContain('United Kingdom');
            expect(document.body.textContent).toContain('Standard');

            respond = (request) => indexPage(new URL(request.url, window.location.origin).pathname);
            await leave('/panel/settings/shipping/zones');

            expect(confirmMock).toHaveBeenCalledWith('common.leave_unsaved_confirm');
            expect(window.location.pathname).toBe(ZONE_URL);
            expect(beforeUnloadPrevented()).toBe(true);

            // Saving the zone sends the country and clears the guard.
            confirmMock.mockClear();
            respond = () => zonePage([UNITED_KINGDOM], [rate]);
            await clickButton('common.save');

            const put = requests.at(-1)!;
            expect(put.method).toBe('put');
            expect((put.data as { countries: number[] }).countries).toEqual([UNITED_KINGDOM]);

            respond = (request) => indexPage(new URL(request.url, window.location.origin).pathname);
            await leave('/panel/settings/shipping/zones');

            expect(confirmMock).not.toHaveBeenCalled();
            expect(window.location.pathname).toBe('/panel/settings/shipping/zones');
        });
    });

    describe('shipping exclusion list', () => {
        it('marks the page dirty when products are picked, and sends them on save', async () => {
            await mountPage(exclusionListPage([]));

            await clickButton('discounts.target_add');
            await click('[data-pick="mug"]');

            respond = (request) => indexPage(new URL(request.url, window.location.origin).pathname);
            await leave('/panel/settings/shipping/exclusion-lists');

            expect(confirmMock).toHaveBeenCalledWith('common.leave_unsaved_confirm');
            expect(window.location.pathname).toBe('/panel/settings/shipping/exclusion-lists/2/edit');

            confirmMock.mockClear();
            respond = () => exclusionListPage([{ id: 7, label: 'Mug', hint: null }]);
            await clickButton('common.save');

            expect((requests.at(-1)!.data as { products: number[] }).products).toEqual([7]);

            respond = (request) => indexPage(new URL(request.url, window.location.origin).pathname);
            await leave('/panel/settings/shipping/exclusion-lists');

            expect(confirmMock).not.toHaveBeenCalled();
        });
    });
});
