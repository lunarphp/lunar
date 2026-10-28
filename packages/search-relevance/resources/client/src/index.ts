/**
 * Storefront click tracking for lunarphp/search-relevance.
 *
 * Reads the `meta` the results pipeline stamps on SearchResults and each hit,
 * and posts a click to the events endpoint in a way that survives the
 * navigation the click usually starts.
 */

export type HitSource = 'organic' | 'learned' | 'explore';

export interface TrackedResults {
    meta?: { search_id?: string | null; [key: string]: unknown };
}

export interface TrackedHit {
    document: { id?: string | number; [key: string]: unknown };
    meta?: { position?: number; source?: HitSource; [key: string]: unknown };
}

export interface SearchEventPayload {
    search_id: string;
    product_id: string | number;
    position: number;
    source?: HitSource;
    /** Explicit shopper id for clients without a cart session cookie. */
    session_id?: string;
}

export interface TrackingOptions {
    /** Defaults to `/lunar/search/events` (route `lunar.search-relevance.events`). */
    endpoint?: string;
    /** CSRF token; read from `<meta name="csrf-token">` when omitted. */
    token?: string | null;
    /** Sent as `session_id` with every event. */
    sessionId?: string | null;
}

export const DEFAULT_ENDPOINT = '/lunar/search/events';

export const ATTRIBUTES = {
    searchId: 'data-lunar-search-id',
    productId: 'data-lunar-product-id',
    position: 'data-lunar-position',
    source: 'data-lunar-source',
} as const;

const csrfToken = (): string | null =>
    typeof document === 'undefined' ? null : document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? null;

/**
 * Post one event. Uses `navigator.sendBeacon` so a click that immediately
 * navigates away still delivers, falling back to a keepalive fetch.
 */
export function sendSearchEvent(payload: SearchEventPayload, options: TrackingOptions = {}): void {
    const endpoint = options.endpoint ?? DEFAULT_ENDPOINT;
    const token = options.token === undefined ? csrfToken() : options.token;
    const data = new FormData();

    data.append('search_id', String(payload.search_id));
    data.append('product_id', String(payload.product_id));
    data.append('position', String(payload.position));
    data.append('source', payload.source ?? 'organic');

    const sessionId = payload.session_id ?? options.sessionId;
    if (sessionId) {
        data.append('session_id', sessionId);
    }
    if (token) {
        data.append('_token', token);
    }

    if (typeof navigator !== 'undefined' && typeof navigator.sendBeacon === 'function' && navigator.sendBeacon(endpoint, data)) {
        return;
    }

    void fetch(endpoint, { method: 'POST', body: data, keepalive: true, credentials: 'same-origin' }).catch(() => undefined);
}

/** The payload for a hit, or null when the search was not logged. */
export function eventFor(results: TrackedResults, hit: TrackedHit): SearchEventPayload | null {
    const searchId = results.meta?.search_id;
    const productId = hit.document?.id;

    if (!searchId || productId === undefined || productId === null) {
        return null;
    }

    return {
        search_id: searchId,
        product_id: productId,
        position: Number(hit.meta?.position ?? 0),
        source: hit.meta?.source ?? 'organic',
    };
}

/** Record a click on a hit. No-op when the search was not logged. */
export function trackHit(results: TrackedResults, hit: TrackedHit, options: TrackingOptions = {}): void {
    const payload = eventFor(results, hit);

    if (payload) {
        sendSearchEvent(payload, options);
    }
}

/**
 * The `data-lunar-*` attributes for a rendered hit, for storefronts that
 * prefer markup plus `attachSearchTracking()` over calling trackHit().
 */
export function trackingAttributes(results: TrackedResults, hit: TrackedHit): Record<string, string> {
    const payload = eventFor(results, hit);

    if (!payload) {
        return {};
    }

    return {
        [ATTRIBUTES.searchId]: payload.search_id,
        [ATTRIBUTES.productId]: String(payload.product_id),
        [ATTRIBUTES.position]: String(payload.position),
        [ATTRIBUTES.source]: payload.source ?? 'organic',
    };
}

export function payloadFromElement(element: Element): SearchEventPayload | null {
    const searchId = element.getAttribute(ATTRIBUTES.searchId);
    const productId = element.getAttribute(ATTRIBUTES.productId);

    if (!searchId || productId === null) {
        return null;
    }

    return {
        search_id: searchId,
        product_id: productId,
        position: Number(element.getAttribute(ATTRIBUTES.position) ?? 0),
        source: (element.getAttribute(ATTRIBUTES.source) as HitSource | null) ?? 'organic',
    };
}

/**
 * Delegated click tracking: any click inside an element carrying the
 * tracking attributes sends an event. Returns a function that detaches it.
 */
export function attachSearchTracking(options: TrackingOptions = {}, root: Document | Element = document): () => void {
    const handler = (event: Event): void => {
        const target = event.target instanceof Element ? event.target.closest(`[${ATTRIBUTES.searchId}]`) : null;
        const payload = target ? payloadFromElement(target) : null;

        if (payload) {
            sendSearchEvent(payload, options);
        }
    };

    root.addEventListener('click', handler, true);

    return () => root.removeEventListener('click', handler, true);
}

/** Shape published on window by the IIFE build. */
export const attach = attachSearchTracking;
