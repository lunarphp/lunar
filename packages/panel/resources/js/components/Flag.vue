<script setup lang="ts">
import { computed } from 'vue';

// Renders a country/region flag from an ISO 3166-1 alpha-2 code via flag-icons
// (SVG, so it renders identically on every OS -- unlike emoji flags, which
// Windows has no glyphs for). Unknown or malformed codes render nothing.
const props = withDefaults(
    defineProps<{
        code?: string | null;
        label?: string;
        squared?: boolean;
    }>(),
    { code: null, label: undefined, squared: false },
);

const normalized = computed(() => (props.code ?? '').trim().toLowerCase());
const valid = computed(() => /^[a-z]{2}$/.test(normalized.value));
</script>

<template>
    <span
        v-if="valid"
        :class="['fi', squared ? 'fis' : '', `fi-${normalized}`, 'rounded-[2px] shadow-[inset_0_0_0_1px_rgba(0,0,0,0.08)]']"
        role="img"
        :aria-label="label ?? normalized.toUpperCase()"
    />
</template>
