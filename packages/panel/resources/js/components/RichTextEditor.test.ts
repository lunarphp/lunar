import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import RichTextEditor from './RichTextEditor.vue';

describe('RichTextEditor', () => {
    it('renders the toolbar and the initial HTML content', async () => {
        const wrapper = mount(RichTextEditor, {
            props: { modelValue: '<p>Hello <strong>world</strong></p>' },
        });

        await nextTick();

        expect(wrapper.find('[aria-label="Bold"]').exists()).toBe(true);
        expect(wrapper.find('[aria-label="Heading 1"]').exists()).toBe(true);

        // The editor mounts its content asynchronously.
        await expect.poll(() => wrapper.html()).toContain('Hello');
    });

    it('applies external model changes to the editor', async () => {
        const wrapper = mount(RichTextEditor, { props: { modelValue: '<p>One</p>' } });

        await nextTick();
        await wrapper.setProps({ modelValue: '<p>Two</p>' });
        await nextTick();

        expect(wrapper.html()).toContain('Two');
    });
});
