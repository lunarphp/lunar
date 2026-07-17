import { afterEach, describe, expect, it } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';
import DraftConflictDialog from './DraftConflictDialog.vue';
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

async function mountDialog(conflicts: DraftConflict[]): Promise<VueWrapper> {
    wrapper = mount(DraftConflictDialog, {
        props: { open: true, conflicts },
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
});
