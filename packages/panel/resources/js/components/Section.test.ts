import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import Section from './Section.vue';

describe('Section', () => {
    it('shows the applies-immediately marker, with its explanation, only when asked', () => {
        const plain = mount(Section, { props: { title: 'Basics' } });
        const immediate = mount(Section, { props: { title: 'Inventory', immediate: true } });

        expect(plain.find('[data-testid="applies-immediately"]').exists()).toBe(false);
        expect(immediate.find('h2 [data-testid="applies-immediately"]').text()).toContain('drafts.applies_immediately');
        expect(immediate.find('h2 [data-tooltip]').attributes('data-tooltip')).toBe('drafts.applies_immediately_tip');
    });
});
