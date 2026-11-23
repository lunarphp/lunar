<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Icon from './Icon.vue';
import Tooltip from './Tooltip.vue';

// The marker for a control that is not part of the edit draft: it takes
// effect the moment it runs, and Discard does not undo it.
const { t } = useI18n();
</script>

<template>
    <Tooltip :text="t('drafts.applies_immediately_tip')">
        <span
            tabindex="0"
            class="inline-flex items-center gap-1 rounded-full border border-line bg-surface-2 px-2 py-[1px] text-[10.5px] font-medium text-ink-500 whitespace-nowrap focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
            data-testid="applies-immediately"
        >
            <Icon name="info" cls="sm" />
            {{ t('drafts.applies_immediately') }}
        </span>
    </Tooltip>
</template>
