/**
 * Storefront click tracking for lunarphp/search-relevance.
 *
 * Reads the `meta` the results pipeline stamps on SearchResults and each hit,
 * and posts a click to the events endpoint in a way that survives the
 * navigation the click usually starts.
 */
export type HitSource = 'organic' | 'learned' | 'explore';
export interface TrackedResults {
    meta?: {
        search_id?: string | null;
        [key: string]: unknown;
    };
}
export interface TrackedHit {
    document: {
        id?: string | number;
        [key: string]: unknown;
    };
    meta?: {
        position?: number;
        source?: HitSource;
        [key: string]: unknown;
    };
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
export declare const DEFAULT_ENDPOINT = "/lunar/search/events";
export declare const ATTRIBUTES: {
    readonly searchId: "data-lunar-search-id";
    readonly productId: "data-lunar-product-id";
    readonly position: "data-lunar-position";
    readonly source: "data-lunar-source";
};
/**
 * Post one event. Uses `navigator.sendBeacon` so a click that immediately
 * navigates away still delivers, falling back to a keepalive fetch.
 */
export declare function sendSearchEvent(payload: SearchEventPayload, options?: TrackingOptions): void;
/** The payload for a hit, or null when the search was not logged. */
export declare function eventFor(results: TrackedResults, hit: TrackedHit): SearchEventPayload | null;
/** Record a click on a hit. No-op when the search was not logged. */
export declare function trackHit(results: TrackedResults, hit: TrackedHit, options?: TrackingOptions): void;
/**
 * The `data-lunar-*` attributes for a rendered hit, for storefronts that
 * prefer markup plus `attachSearchTracking()` over calling trackHit().
 */
export declare function trackingAttributes(results: TrackedResults, hit: TrackedHit): Record<string, string>;
export declare function payloadFromElement(element: Element): SearchEventPayload | null;
/**
 * Delegated click tracking: any click inside an element carrying the
 * tracking attributes sends an event. Returns a function that detaches it.
 */
export declare function attachSearchTracking(options?: TrackingOptions, root?: Document | Element): () => void;
/** Shape published on window by the IIFE build. */
export declare const attach: typeof attachSearchTracking;
