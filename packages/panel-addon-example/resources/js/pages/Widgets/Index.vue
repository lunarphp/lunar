<script setup lang="ts">
// The sidebar shell (PanelLayout) is auto-applied to add-on pages by the panel,
// so this page only builds its own header and content. Every component here comes
// from `@lunarphp/panel`, which the add-on vite plugin externalises to the panel's
// runtime — no panel components are bundled into this add-on. `@inertiajs/vue3`
// is externalised the same way, so `usePage()`/`<Link>` below share the panel's
// live Inertia instance rather than a bundled second copy.
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
// `vue-i18n` is externalised to the panel's own instance, whose message store
// holds this add-on's PHP lang groups (opted in via Section::langNamespaces())
// under `example-addon::{group}` keys — served, cached and versioned by the
// panel's translations endpoint like the panel's own strings.
import { useI18n } from 'vue-i18n';
import { Breadcrumbs, DataTable, PageHeader, PageZone, Button, SideCard, StatusBadge } from '@lunarphp/panel';

defineProps<{
    message?: string;
    widgets?: Record<string, unknown>[];
}>();

const { t } = useI18n();

const breadcrumbs = computed(() => [
    { label: 'Add-ons' },
    { label: t('example-addon::example.title'), current: true },
]);

// Shared props the panel middleware provides to every page, add-on pages included.
const panelName = computed(() => (usePage().props.panel as { name?: string } | undefined)?.name ?? 'Lunar');

const flashSuccess = computed(() => (usePage().props.flash as { success?: string } | undefined)?.success);

// Same column shape first-party pages use: key/label plus optional width and align.
const columns = [
    { key: 'name', label: 'Widget', width: 'minmax(0,1.4fr)' },
    { key: 'status', label: 'Status' },
    { key: 'sales', label: 'Sales (30d)', width: '110px', align: 'right' as const },
];

// Static descriptors; each row's target URL comes from its server-provided
// _actions map, so an action only renders on rows that resolved a URL for it.
const rowActions = [
    { key: 'ping', label: 'Ping', icon: 'refresh', method: 'get', primary: false },
];
</script>

<template>
    <div data-screen-label="Example Add-on" class="contents">
        <Breadcrumbs :items="breadcrumbs" />

        <PageHeader
            :title="t('example-addon::example.title')"
            :description="t('example-addon::example.description')"
            icon="tag"
        >
            <template #actions>
                <Button variant="primary" icon="plus">Example action</Button>
            </template>
        </PageHeader>

        <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
            <PageZone region="main" position="before" />

            <div v-if="flashSuccess" class="mb-4 rounded-md border border-sage-border bg-sage-soft px-3 py-2 text-[12px] text-sage-ink">
                {{ flashSuccess }}
            </div>

            <p class="text-[13px] text-ink-700">
                {{ message ?? 'This page is served by a separately-compiled add-on package.' }}
            </p>

            <div class="mt-5">
                <DataTable
                    :columns="columns"
                    :rows="widgets ?? []"
                    :row-actions="rowActions"
                    empty-text="No widgets yet"
                >
                    <template #cell-status="{ value }">
                        <StatusBadge :tone="value === 'active' ? 'sage' : 'archived'" size="sm">{{ value }}</StatusBadge>
                    </template>
                </DataTable>
            </div>

            <SideCard title="Reusing panel components" class="mt-5 max-w-md">
                <p class="text-[12.5px] text-ink-700">
                    This card, its badge, and the header above are all the panel's own
                    components, imported from <code>@lunarphp/panel</code>. The panel
                    name below comes from <code>usePage()</code> and the link routes
                    through Inertia — both via the panel's shared
                    <code>@inertiajs/vue3</code> instance.
                </p>
                <div class="mt-2 flex items-center gap-2">
                    <StatusBadge tone="sage" size="sm">{{ panelName }}</StatusBadge>
                    <Link href="/panel/customers" class="text-[12.5px] text-ink-700 underline underline-offset-2 hover:text-ink-900">
                        Customers
                    </Link>
                </div>
            </SideCard>

            <PageZone region="main" position="after" />
        </div>
    </div>
</template>
