import { afterEach, describe, expect, it } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';
import DraftConflictDialog, { type ConflictField } from './DraftConflictDialog.vue';
import { priceConflictField } from './pricing';
import type { DraftConflict } from '../lib/http';

// Reka's DialogPortal teleports content to document.body, out of the
// wrapper's reach; interactions query the document instead.
const body = (): HTMLElement => document.body;

const textConflict: DraftConflict = {
    key: 'first_name',
    label: 'First name',
    mine: 'Mine',
    base: 'Base',
    theirs: 'Theirs',
};

const arrayConflict: DraftConflict = {
    key: 'customer_group_ids',
    label: 'Customer groups',
    mine: [1, 2],
    base: [1],
    theirs: [1, 3],
};

let wrapper: VueWrapper | null = null;

async function mountDialog(
    conflicts: DraftConflict[],
    extra: { field?: (key: string) => ConflictField | undefined; errors?: Record<string, string> } = {},
): Promise<VueWrapper> {
    wrapper = mount(DraftConflictDialog, {
        props: { open: true, conflicts, ...extra },
        attachTo: document.body,
    });

    // The portal teleports content to document.body on the next tick.
    await nextTick();

    return wrapper;
}

function clickButtonByText(text: string): Promise<void> {
    const button = [...body().querySelectorAll('button')].find((el) => el.textContent?.includes(text));

    expect(button, `button containing "${text}"`).toBeDefined();
    button?.click();

    return nextTick();
}

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    document.body.innerHTML = '';
});

describe('DraftConflictDialog', () => {
    it('renders a row per conflict with both values and the base note', async () => {
        await mountDialog([textConflict, arrayConflict]);

        const text = body().textContent ?? '';

        expect(text).toContain('First name');
        expect(text).toContain('Mine');
        expect(text).toContain('Theirs');
        expect(text).toContain('Customer groups');
        expect(text).toContain('1, 2');
        expect(text).toContain('1, 3');
    });

    it('defaults to keeping mine and emits theirs as the rebase pin', async () => {
        const dialog = await mountDialog([textConflict]);

        await clickButtonByText('drafts.apply');

        expect(dialog.emitted('resolve')).toEqual([[{ first_name: 'Mine' }, { first_name: 'Theirs' }]]);
    });

    it('emits the current value when take-theirs is chosen', async () => {
        const dialog = await mountDialog([textConflict]);

        await clickButtonByText('drafts.current_value');
        await clickButtonByText('drafts.apply');

        expect(dialog.emitted('resolve')).toEqual([[{ first_name: 'Theirs' }, { first_name: 'Theirs' }]]);
    });

    it('lets a manual edit override the chosen scalar value', async () => {
        const dialog = await mountDialog([textConflict]);

        const input = body().querySelector<HTMLInputElement>('input[type="text"]');
        expect(input).not.toBeNull();

        input!.value = 'Merged by hand';
        input!.dispatchEvent(new Event('input', { bubbles: true }));
        await nextTick();

        await clickButtonByText('drafts.apply');

        expect(dialog.emitted('resolve')).toEqual([[{ first_name: 'Merged by hand' }, { first_name: 'Theirs' }]]);
    });

    it('keeps a manual edit to a numeric field numeric', async () => {
        const dialog = await mountDialog([{ key: 'sort', label: 'Sort', mine: 3, base: 1, theirs: 2 }]);

        const input = body().querySelector<HTMLInputElement>('input[type="text"]');
        input!.value = '5';
        input!.dispatchEvent(new Event('input', { bubbles: true }));
        await nextTick();

        await clickButtonByText('drafts.apply');

        expect(dialog.emitted('resolve')).toEqual([[{ sort: 5 }, { sort: 2 }]]);
    });

    it('keeps a manual edit to a boolean field boolean', async () => {
        const dialog = await mountDialog([{ key: 'shippable', label: 'Shippable', mine: true, base: true, theirs: false }]);

        const input = body().querySelector<HTMLInputElement>('input[type="text"]');
        input!.value = 'false';
        input!.dispatchEvent(new Event('input', { bubbles: true }));
        await nextTick();

        await clickButtonByText('drafts.apply');

        expect(dialog.emitted('resolve')).toEqual([[{ shippable: false }, { shippable: false }]]);
    });

    it('shows a translated field as its text rather than an object', async () => {
        await mountDialog([
            { key: 'name', label: 'Name', mine: { en: 'Mine' }, base: { en: 'Base' }, theirs: { en: 'Theirs', fr: 'Les leurs' } },
        ]);

        const text = body().textContent ?? '';

        expect(text).toContain('Mine');
        expect(text).toContain('en: Theirs · fr: Les leurs');
        expect(text).not.toContain('[object Object]');
    });

    it('offers no manual edit input for structured values', async () => {
        await mountDialog([arrayConflict]);

        expect(body().querySelector('input[type="text"]')).toBeNull();
    });

    it('closes without resolving on cancel', async () => {
        const dialog = await mountDialog([textConflict]);

        await clickButtonByText('common.cancel');

        expect(dialog.emitted('resolve')).toBeUndefined();
        expect(dialog.emitted('update:open')).toEqual([[false]]);
    });

    it('shows a price list as money with its currency rather than JSON', async () => {
        const row = { customer_group_id: null, min_quantity: 1, list_price: null };
        const currencies = [{ id: 1, code: 'USD', decimal_places: 2, default: true }];
        const groups = [{ id: 7, name: 'Wholesale' }];

        await mountDialog(
            [{
                key: 'variant:price:USD',
                label: 'USD',
                mine: [{ ...row, price: 3300 }, { customer_group_id: 7, min_quantity: 10, price: 2500, list_price: null }],
                base: [{ ...row, price: 3000 }],
                theirs: [{ ...row, price: 3500, list_price: 4000 }],
            }],
            { field: (key) => priceConflictField(key, 'variant:price:', currencies, groups, (key) => key) },
        );

        const text = body().textContent ?? '';

        expect(text).toContain('$33.00');
        expect(text).toContain('$25.00 · Wholesale · pricing.tier_min_prefix 10');
        expect(text).toContain('$35.00 · pricing.compare_at $40.00');
        expect(text).not.toContain('customer_group_id');
        expect(body().querySelector('input[type="text"]')).toBeNull();
    });

    it('merges a select field through a select of its option labels', async () => {
        const dialog = await mountDialog(
            [{ key: 'status', label: 'Status', mine: 'draft', base: 'draft', theirs: 'published' }],
            {
                field: (key) => key === 'status'
                    ? { options: [{ value: 'draft', label: 'Draft' }, { value: 'published', label: 'Published' }, { value: 'archived', label: 'Archived' }] }
                    : undefined,
            },
        );

        expect(body().textContent).toContain('Published');
        expect(body().querySelector('input[type="text"]')).toBeNull();

        const select = body().querySelector<HTMLSelectElement>('select');
        expect(select).not.toBeNull();
        expect([...select!.options].map((option) => option.textContent)).toEqual(['Draft', 'Published', 'Archived']);

        select!.selectedIndex = 2;
        select!.dispatchEvent(new Event('change', { bubbles: true }));
        await nextTick();

        await clickButtonByText('drafts.apply');

        expect(dialog.emitted('resolve')).toEqual([[{ status: 'archived' }, { status: 'published' }]]);
    });

    it('shows the commit error for a refused resolution', async () => {
        await mountDialog([textConflict], { errors: { first_name: 'The first name is invalid.', email: 'The email is taken.' } });

        const alerts = [...body().querySelectorAll('[role="alert"]')].map((el) => el.textContent?.trim());

        expect(alerts).toContain('The first name is invalid.');
        expect(alerts).toContain('The email is taken.');
    });
});
