import type { Directive, MaybeRefOrGetter } from 'vue';
import { sendSearchEvent } from './index';
import type { TrackedHit, TrackedResults, TrackingOptions } from './index';
export * from './index';
/**
 * Click tracking for an Inertia or Vue storefront:
 *
 *   const { track, attrs } = useSearchTracking(() => props.results);
 *   <a v-bind="attrs(hit)" @click="track(hit)" :href="...">
 */
export declare function useSearchTracking(results: MaybeRefOrGetter<TrackedResults | null | undefined>, options?: TrackingOptions): {
    /** Send a click event for a hit. Safe to call when the search was not logged. */
    track: (hit: TrackedHit) => void;
    /** The `data-lunar-*` attributes for a hit, for `v-bind`. */
    attrs: (hit: TrackedHit) => Record<string, string>;
    /** The raw sender, for events not tied to a rendered hit. */
    send: (payload: Parameters<typeof sendSearchEvent>[0]) => void;
};
interface HitDirectiveValue {
    results: TrackedResults;
    hit: TrackedHit;
    options?: TrackingOptions;
}
/**
 * `v-lunar-search-hit="{ results, hit }"`: stamps the tracking attributes on
 * the element and sends a click event when anything inside it is clicked.
 */
export declare const vLunarSearchHit: Directive<HTMLElement, HitDirectiveValue>;
