<script setup lang="ts">
import { computed, reactive } from 'vue';

interface ActivityEvent {
    id?: string | number;
    label?: string;
    detail?: string;
    when?: string;
    actor?: string;
    /** Gravatar (or other) avatar URL; falls back to an initials avatar when absent or unreachable. */
    avatar?: string | null;
    /** Changed column names for an update event; drives the "what changed" line. */
    changes?: string[];
    /** spatie/activitylog shape — falls back to `label` when set. */
    description?: string;
    /** spatie/activitylog shape — falls back to `when` when set. */
    created_at?: string;
    /** spatie/activitylog shape — falls back to `actor` when set. */
    causer_name?: string | null;
}

const props = withDefaults(
    defineProps<{
        events: ActivityEvent[];
        // newest first by default; events come in ascending order from the data layer.
        reverse?: boolean;
        // Shown as the actor when an event has no causer (system-generated).
        systemLabel?: string;
    }>(),
    { reverse: true, systemLabel: 'System' },
);

const ordered = computed(() => (props.reverse ? [...props.events].reverse() : props.events));

const eventLabel = (ev: ActivityEvent): string => ev.label ?? ev.description ?? '';
const eventActor = (ev: ActivityEvent): string => (ev.actor ?? ev.causer_name ?? '') || props.systemLabel;
const isSystem = (ev: ActivityEvent): boolean => !(ev.actor ?? ev.causer_name);

// Avatar URLs that failed to load (e.g. Gravatar 404) fall back to initials.
const failedAvatars = reactive(new Set<string>());
const avatarUrl = (ev: ActivityEvent): string =>
    ev.avatar && !failedAvatars.has(ev.avatar) ? ev.avatar : '';

// Raw column names cleaned up for display. A handful read badly under the generic
// rule, so they're spelled out; everything else is snake_case -> Title Case with a
// trailing "_id" dropped (product_type_id -> "Product type").
const FIELD_LABELS: Record<string, string> = {
    attribute_data: 'Attributes',
    sku: 'SKU',
    ean: 'EAN',
    gtin: 'GTIN',
    mpn: 'MPN',
};

const humanizeField = (key: string): string =>
    FIELD_LABELS[key] ??
    key
        .replace(/_id$/, '')
        .replaceAll('_', ' ')
        .replace(/^\w/, (c) => c.toUpperCase());

const changedFields = (ev: ActivityEvent): string =>
    (ev.changes ?? []).map(humanizeField).join(', ');

const initials = (name: string): string => {
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) {
        return '?';
    }
    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
    }
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

// Deterministic hue from the name so a given person keeps the same avatar colour.
const avatarStyle = (ev: ActivityEvent): Record<string, string> => {
    if (isSystem(ev)) {
        return { backgroundColor: 'hsl(220 9% 60%)' };
    }
    const name = eventActor(ev);
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = (hash * 31 + name.charCodeAt(i)) >>> 0;
    }
    return { backgroundColor: `hsl(${hash % 360} 52% 45%)` };
};

const RELATIVE_UNITS: [Intl.RelativeTimeFormatUnit, number][] = [
    ['year', 31536000],
    ['month', 2592000],
    ['week', 604800],
    ['day', 86400],
    ['hour', 3600],
    ['minute', 60],
];

const relativeTime = (ev: ActivityEvent): string => {
    const raw = ev.when ?? ev.created_at;
    if (!raw) {
        return '';
    }
    const then = new Date(raw).getTime();
    if (Number.isNaN(then)) {
        return String(raw);
    }
    const diff = Math.round((then - Date.now()) / 1000);
    const abs = Math.abs(diff);
    if (abs < 60) {
        return 'just now';
    }
    const rtf = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto', style: 'narrow' });
    for (const [unit, secs] of RELATIVE_UNITS) {
        if (abs >= secs) {
            return rtf.format(Math.round(diff / secs), unit);
        }
    }
    return 'just now';
};

const absoluteTime = (ev: ActivityEvent): string => {
    const raw = ev.when ?? ev.created_at;
    if (!raw) {
        return '';
    }
    const date = new Date(raw);
    return Number.isNaN(date.getTime()) ? String(raw) : date.toLocaleString();
};
</script>

<template>
    <ol class="relative pl-1">
        <li
            v-for="(ev, i) in ordered"
            :key="ev.id ?? i"
            class="relative flex gap-3 pb-4 last:pb-0"
        >
            <!-- Vertical connector (excluding last item) -->
            <span
                v-if="i < ordered.length - 1"
                class="absolute left-[15px] top-8 bottom-0 w-px bg-line"
                aria-hidden="true"
            />
            <img
                v-if="avatarUrl(ev)"
                :src="avatarUrl(ev)"
                :alt="eventActor(ev)"
                class="relative z-10 w-[30px] h-[30px] rounded-full shrink-0 object-cover bg-surface-2"
                @error="ev.avatar && failedAvatars.add(ev.avatar)"
            />
            <div
                v-else
                class="relative z-10 w-[30px] h-[30px] rounded-full grid place-items-center shrink-0 text-[10px] font-semibold text-white select-none"
                :style="avatarStyle(ev)"
                :aria-label="eventActor(ev)"
            >
                {{ initials(eventActor(ev)) }}
            </div>
            <div class="flex-1 min-w-0 pt-0.5">
                <div class="text-[13px] leading-snug">
                    <span class="font-semibold text-ink-900">{{ eventActor(ev) }}</span>
                    <span v-if="eventLabel(ev)" class="text-ink-600">&nbsp;&middot;&nbsp;{{ eventLabel(ev) }}</span>
                </div>
                <div v-if="changedFields(ev)" class="text-xs text-ink-500 mt-1">
                    {{ changedFields(ev) }}
                </div>
                <div v-else-if="ev.detail" class="text-xs text-ink-500 mt-1">{{ ev.detail }}</div>
                <div class="text-[11px] text-ink-400 mt-1" :title="absoluteTime(ev)">
                    {{ relativeTime(ev) }}
                </div>
            </div>
        </li>
    </ol>
</template>
