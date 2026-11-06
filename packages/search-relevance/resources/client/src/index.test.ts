import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { attachSearchTracking, eventFor, sendSearchEvent, trackingAttributes } from './index';

const results = { meta: { search_id: '01ARZ3NDEKTSV4RRFFQ69G5FAV' } };
const hit = { document: { id: '7' }, meta: { position: 3, source: 'learned' as const } };

const formEntries = (data: FormData): Record<string, string> => Object.fromEntries([...data.entries()].map(([k, v]) => [k, String(v)]));

describe('sendSearchEvent', () => {
    beforeEach(() => {
        document.head.innerHTML = '<meta name="csrf-token" content="tok">';
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('prefers sendBeacon and includes the CSRF token from the page', () => {
        const beacon = vi.fn(() => true);
        Object.defineProperty(navigator, 'sendBeacon', { value: beacon, configurable: true });
        const fetchSpy = vi.spyOn(globalThis, 'fetch');

        sendSearchEvent({ search_id: 'abc', product_id: 7, position: 2 });

        expect(beacon).toHaveBeenCalledOnce();
        const [endpoint, data] = beacon.mock.calls[0] as unknown as [string, FormData];
        expect(endpoint).toBe('/lunar/search/events');
        expect(formEntries(data)).toEqual({ search_id: 'abc', product_id: '7', position: '2', source: 'organic', _token: 'tok' });
        expect(fetchSpy).not.toHaveBeenCalled();
    });

    it('falls back to a keepalive fetch and passes the session id', async () => {
        Object.defineProperty(navigator, 'sendBeacon', { value: undefined, configurable: true });
        const fetchSpy = vi.spyOn(globalThis, 'fetch').mockResolvedValue(new Response(null, { status: 204 }));

        sendSearchEvent({ search_id: 'abc', product_id: 7, position: 2, source: 'learned' }, { endpoint: '/events', token: null, sessionId: 'cart:9' });

        expect(fetchSpy).toHaveBeenCalledOnce();
        const [endpoint, init] = fetchSpy.mock.calls[0] as [string, RequestInit];
        expect(endpoint).toBe('/events');
        expect(init.keepalive).toBe(true);
        expect(formEntries(init.body as FormData)).toEqual({ search_id: 'abc', product_id: '7', position: '2', source: 'learned', session_id: 'cart:9' });
    });
});

describe('eventFor and trackingAttributes', () => {
    it('builds the payload from the results and hit meta', () => {
        expect(eventFor(results, hit)).toEqual({ search_id: '01ARZ3NDEKTSV4RRFFQ69G5FAV', product_id: '7', position: 3, source: 'learned' });
        expect(trackingAttributes(results, hit)).toEqual({
            'data-lunar-search-id': '01ARZ3NDEKTSV4RRFFQ69G5FAV',
            'data-lunar-product-id': '7',
            'data-lunar-position': '3',
            'data-lunar-source': 'learned',
        });
    });

    it('is empty when the search was not logged', () => {
        expect(eventFor({ meta: {} }, hit)).toBeNull();
        expect(trackingAttributes({}, hit)).toEqual({});
    });
});

describe('attachSearchTracking', () => {
    it('sends an event for clicks inside a tracked element and can detach', () => {
        const beacon = vi.fn(() => true);
        Object.defineProperty(navigator, 'sendBeacon', { value: beacon, configurable: true });
        document.body.innerHTML = '<div data-lunar-search-id="abc" data-lunar-product-id="7" data-lunar-position="2" data-lunar-source="organic"><a id="link">Go</a></div><a id="other">No</a>';

        const detach = attachSearchTracking({ token: null });

        document.getElementById('link')!.click();
        document.getElementById('other')!.click();
        expect(beacon).toHaveBeenCalledOnce();
        expect(formEntries(beacon.mock.calls[0]![1] as FormData)).toEqual({ search_id: 'abc', product_id: '7', position: '2', source: 'organic' });

        detach();
        document.getElementById('link')!.click();
        expect(beacon).toHaveBeenCalledOnce();
    });
});
