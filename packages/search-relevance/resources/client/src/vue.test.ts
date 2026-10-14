import { describe, expect, it, vi } from 'vitest';
import { defineComponent, h, nextTick, ref } from 'vue';
import { mount } from './test-utils';
import { useSearchTracking, vLunarSearchHit } from './vue';

const results = { meta: { search_id: '01ARZ3NDEKTSV4RRFFQ69G5FAV' } };
const hit = { document: { id: '7' }, meta: { position: 3, source: 'organic' as const } };

describe('useSearchTracking', () => {
    it('tracks a hit against the current results and exposes bindable attributes', () => {
        const beacon = vi.fn(() => true);
        Object.defineProperty(navigator, 'sendBeacon', { value: beacon, configurable: true });
        const current = ref<{ meta: { search_id?: string } }>({ meta: {} });
        const { track, attrs } = useSearchTracking(current, { token: null });

        track(hit);
        expect(beacon).not.toHaveBeenCalled();
        expect(attrs(hit)).toEqual({});

        current.value = results;
        track(hit);
        expect(beacon).toHaveBeenCalledOnce();
        expect(attrs(hit)['data-lunar-search-id']).toBe(results.meta.search_id);
    });
});

describe('vLunarSearchHit', () => {
    it('stamps the attributes and sends a click event', async () => {
        const beacon = vi.fn(() => true);
        Object.defineProperty(navigator, 'sendBeacon', { value: beacon, configurable: true });

        const Component = defineComponent({
            directives: { lunarSearchHit: vLunarSearchHit },
            setup: () => () => h('div', { id: 'hit' }, [h('a', 'Go')]),
        });
        const element = mount(
            defineComponent({
                setup: () => () => h('div', [
                    // Wrapper so the directive binds inside the component tree.
                    h(Component),
                ]),
            }),
        );

        const target = element.querySelector('#hit') as HTMLElement;
        vLunarSearchHit.mounted!(target, { value: { results, hit, options: { token: null } } } as never, null as never, null as never);
        await nextTick();

        expect(target.getAttribute('data-lunar-position')).toBe('3');
        (target.querySelector('a') as HTMLElement).click();
        expect(beacon).toHaveBeenCalledOnce();
    });
});
