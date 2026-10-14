import type { Directive, MaybeRefOrGetter } from 'vue';
import { toValue } from 'vue';
import { sendSearchEvent, trackHit, trackingAttributes } from './index';
import type { TrackedHit, TrackedResults, TrackingOptions } from './index';

export * from './index';

/**
 * Click tracking for an Inertia or Vue storefront:
 *
 *   const { track, attrs } = useSearchTracking(() => props.results);
 *   <a v-bind="attrs(hit)" @click="track(hit)" :href="...">
 */
export function useSearchTracking(results: MaybeRefOrGetter<TrackedResults | null | undefined>, options: TrackingOptions = {}) {
    const current = (): TrackedResults => toValue(results) ?? {};

    return {
        /** Send a click event for a hit. Safe to call when the search was not logged. */
        track: (hit: TrackedHit): void => trackHit(current(), hit, options),
        /** The `data-lunar-*` attributes for a hit, for `v-bind`. */
        attrs: (hit: TrackedHit): Record<string, string> => trackingAttributes(current(), hit),
        /** The raw sender, for events not tied to a rendered hit. */
        send: (payload: Parameters<typeof sendSearchEvent>[0]): void => sendSearchEvent(payload, options),
    };
}

interface HitDirectiveValue {
    results: TrackedResults;
    hit: TrackedHit;
    options?: TrackingOptions;
}

/**
 * `v-lunar-search-hit="{ results, hit }"`: stamps the tracking attributes on
 * the element and sends a click event when anything inside it is clicked.
 */
export const vLunarSearchHit: Directive<HTMLElement, HitDirectiveValue> = {
    mounted(element, binding) {
        apply(element, binding.value);
        element.addEventListener('click', () => {
            const value = (element as HitDirectiveElement).__lunarSearchHit;
            if (value) {
                trackHit(value.results, value.hit, value.options);
            }
        });
    },
    updated(element, binding) {
        apply(element, binding.value);
    },
};

interface HitDirectiveElement extends HTMLElement {
    __lunarSearchHit?: HitDirectiveValue;
}

function apply(element: HTMLElement, value: HitDirectiveValue): void {
    (element as HitDirectiveElement).__lunarSearchHit = value;

    for (const [name, attributeValue] of Object.entries(trackingAttributes(value.results, value.hit))) {
        element.setAttribute(name, attributeValue);
    }
}
