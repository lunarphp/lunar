import { describe, expect, it } from 'vitest';
import { reactive } from 'vue';
import { mount } from '@vue/test-utils';
import AttributeFields from './AttributeFields.vue';

const field = (overrides: Record<string, unknown>) => ({
    key: `attribute:${overrides.handle}`,
    handle: 'field',
    label: 'Field',
    required: false,
    type: 'text',
    config: {},
    ...overrides,
});

const languages = [{ id: 1, code: 'en', name: 'English', default: true }];

const mountFields = (fields: ReturnType<typeof field>[], values: Record<string, unknown>) =>
    mount(AttributeFields, {
        props: {
            groups: [{ handle: 'general', name: 'General', fields }],
            values,
            errors: {},
            languages,
        },
        attachTo: document.body,
    });

// jsdom reports zero-sized rects, so the drag composable (which reads row
// geometry off the container) has nothing to target. Stub each row at a fixed
// height so a container dragover with a clientY resolves to a known slot.
const ROW_HEIGHT = 30;

const rect = (top: number, height: number): DOMRect =>
    ({ top, height, bottom: top + height, left: 0, right: 0, width: 0, x: 0, y: top, toJSON: () => ({}) }) as DOMRect;

const stubRowGeometry = (container: Element): void => {
    container.getBoundingClientRect = () => rect(0, 0);
    Array.from(container.children).forEach((child, index) => {
        child.getBoundingClientRect = () => rect(index * ROW_HEIGHT, ROW_HEIGHT);
    });
};

// clientY at the vertical centre of the target slot.
const slotY = (index: number): number => index * ROW_HEIGHT + ROW_HEIGHT / 2;

describe('AttributeFields', () => {
    it('maps tokens onto the right inputs', () => {
        const values = reactive<Record<string, unknown>>({
            'attribute:cta': 'Shop now',
            'attribute:finish': 'matte',
            'attribute:featured': true,
        });

        const wrapper = mountFields(
            [
                field({ handle: 'cta', label: 'CTA', type: 'text' }),
                field({ handle: 'finish', label: 'Finish', type: 'dropdown', config: { options: [{ label: 'Matte', value: 'matte' }] } }),
                field({ handle: 'featured', label: 'Featured', type: 'toggle' }),
            ],
            values,
        );

        expect(wrapper.find<HTMLInputElement>('input[aria-label="CTA"]').element.value).toBe('Shop now');
        expect(wrapper.find<HTMLSelectElement>('select').element.value).toBe('matte');
        expect(wrapper.find('button[role="switch"], [data-state]').exists()).toBe(true);
    });

    it('writes edits back into the shared values object', async () => {
        const values = reactive<Record<string, unknown>>({ 'attribute:cta': '' });

        const wrapper = mountFields([field({ handle: 'cta', label: 'CTA', type: 'text' })], values);

        await wrapper.find('input[aria-label="CTA"]').setValue('New CTA');

        expect(values['attribute:cta']).toBe('New CTA');
    });

    it('renders sequential list values as editable rows and appends via the trailing input', async () => {
        const values = reactive<Record<string, unknown>>({ 'attribute:tags': ['alpha'] });

        const wrapper = mountFields([field({ handle: 'tags', label: 'Tags', type: 'list' })], values);

        const firstRow = wrapper.find<HTMLInputElement>('input[aria-label="Tags 1"]');

        expect(firstRow.element.value).toBe('alpha');

        await firstRow.setValue('alpha edited');

        expect(values['attribute:tags']).toEqual(['alpha edited']);

        // Typing in the always-present trailing input appends immediately, with
        // no + button or Enter key needed.
        await wrapper.find('input[aria-label="Tags"]').setValue('beta');

        expect(values['attribute:tags']).toEqual(['alpha edited', 'beta']);
    });

    it('appends to an empty plain list as soon as the user types', async () => {
        const values = reactive<Record<string, unknown>>({ 'attribute:specs': [] });

        const wrapper = mountFields([field({ handle: 'specs', label: 'Specs', type: 'list' })], values);

        await wrapper.find('input[aria-label="Specs"]').setValue('200g');

        expect(values['attribute:specs']).toEqual(['200g']);
    });

    it('prunes a plain row the user empties, on blur', async () => {
        const values = reactive<Record<string, unknown>>({ 'attribute:tags': ['alpha', 'beta'] });

        const wrapper = mountFields([field({ handle: 'tags', label: 'Tags', type: 'list' })], values);

        const first = wrapper.find('input[aria-label="Tags 1"]');
        await first.setValue('');
        await first.trigger('blur');

        expect(values['attribute:tags']).toEqual(['beta']);
    });

    it('auto-commits a complete keyed draft row on blur', async () => {
        const values = reactive<Record<string, unknown>>({ 'attribute:specs': { width: '10cm' } });

        const wrapper = mountFields([field({ handle: 'specs', label: 'Specs', type: 'list' })], values);

        await wrapper.find('input[aria-label="Specs: attributes.list_key_placeholder"]').setValue('height');
        const value = wrapper.find('input[aria-label="Specs"]');
        await value.setValue('20cm');
        await value.trigger('blur');

        expect(values['attribute:specs']).toEqual({ width: '10cm', height: '20cm' });
    });

    it('reorders list rows by dragging the handle', async () => {
        const values = reactive<Record<string, unknown>>({ 'attribute:tags': ['alpha', 'beta', 'gamma'] });

        const wrapper = mountFields([field({ handle: 'tags', label: 'Tags', type: 'list' })], values);

        const handles = wrapper.findAll('button[aria-label="attributes.reorder_item"]');
        const list = wrapper.find('.divide-y');

        stubRowGeometry(list.element);

        await handles[0].trigger('dragstart');
        await list.trigger('dragover', { clientY: slotY(2) });
        await handles[0].trigger('dragend');

        expect(values['attribute:tags']).toEqual(['beta', 'gamma', 'alpha']);
    });

    it('reorders keyed list rows preserving keys', async () => {
        const values = reactive<Record<string, unknown>>({
            'attribute:specs': { width: '10cm', height: '20cm' },
        });

        const wrapper = mountFields([field({ handle: 'specs', label: 'Specs', type: 'list' })], values);

        const handles = wrapper.findAll('button[aria-label="attributes.reorder_item"]');
        const list = wrapper.find('.divide-y');

        stubRowGeometry(list.element);

        await handles[1].trigger('dragstart');
        await list.trigger('dragover', { clientY: slotY(0) });
        await handles[1].trigger('dragend');

        expect(Object.entries(values['attribute:specs'] as Record<string, string>)).toEqual([
            ['height', '20cm'],
            ['width', '10cm'],
        ]);
    });

    it('renders keyed list values (Filament KeyValue shape) with editable entries', async () => {
        const values = reactive<Record<string, unknown>>({
            'attribute:specs': { width: '10cm', height: '20cm' },
        });

        const wrapper = mountFields([field({ handle: 'specs', label: 'Specs', type: 'list' })], values);

        expect(wrapper.text()).toContain('width');

        await wrapper.find('input[aria-label="Specs: width"]').setValue('12cm');

        expect(values['attribute:specs']).toEqual({ width: '12cm', height: '20cm' });
    });

    it('adds and removes keyed list entries without touching other keys', async () => {
        const values = reactive<Record<string, unknown>>({
            'attribute:specs': { width: '10cm', height: '20cm' },
        });

        const wrapper = mountFields([field({ handle: 'specs', label: 'Specs', type: 'list' })], values);

        await wrapper.find('input[aria-label="Specs: attributes.list_key_placeholder"]').setValue('depth');
        await wrapper.find('input[aria-label="Specs"]').setValue('5cm');
        await wrapper.find('button[aria-label="attributes.add_item"]').trigger('click');

        expect(values['attribute:specs']).toEqual({ width: '10cm', height: '20cm', depth: '5cm' });

        await wrapper.find('button[aria-label="attributes.remove_item"]').trigger('click');

        expect(values['attribute:specs']).toEqual({ height: '20cm', depth: '5cm' });
    });

    it('renders unknown field types read-only', () => {
        const values = reactive<Record<string, unknown>>({ 'attribute:custom': 'raw' });

        const wrapper = mountFields([field({ handle: 'custom', label: 'Custom', type: 'unknown' })], values);

        expect(wrapper.find('input[aria-label="Custom"]').exists()).toBe(false);
        expect(wrapper.text()).toContain('attributes.unsupported');
    });

    it('collapses and expands groups', async () => {
        const values = reactive<Record<string, unknown>>({ 'attribute:cta': '' });

        const wrapper = mountFields([field({ handle: 'cta', label: 'CTA', type: 'text' })], values);

        const header = wrapper.find('button[aria-expanded]');

        expect(header.attributes('aria-expanded')).toBe('true');

        await header.trigger('click');

        expect(header.attributes('aria-expanded')).toBe('false');
        // v-show hides the field container (happy-dom's isVisible() does not
        // reflect inline display, so assert the style directly).
        expect(wrapper.find('.border-t').attributes('style')).toContain('display: none');
    });
});
