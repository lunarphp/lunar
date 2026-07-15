<script setup lang="ts">
// The sidebar shell (PanelLayout) is auto-applied to add-on pages by the panel,
// so this page only builds its own header and content. Every component here comes
// from `@lunarphp/panel`, which the add-on vite plugin externalises to the panel's
// runtime — no panel components are bundled into this add-on.
import { Breadcrumbs, PageHeader, PageZone, Button, SideCard, StatusBadge } from '@lunarphp/panel';

defineProps<{
    message?: string;
}>();

const breadcrumbs = [
    { label: 'Add-ons' },
    { label: 'Example Add-on', current: true },
];
</script>

<template>
    <div data-screen-label="Example Add-on" class="contents">
        <Breadcrumbs :items="breadcrumbs" />

        <PageHeader
            title="Example Add-on"
            description="A page contributed by the example add-on package, using the panel's own layout and chrome."
        >
            <template #actions>
                <Button variant="primary" icon="plus">Example action</Button>
            </template>
        </PageHeader>

        <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
            <PageZone region="main" position="before" />

            <p class="text-[13px] text-ink-700">
                {{ message ?? 'This page is served by a separately-compiled add-on package.' }}
            </p>

            <SideCard title="Reusing panel components" class="mt-5 max-w-md">
                <p class="text-[12.5px] text-ink-700">
                    This card, its badge, and the header above are all the panel's own
                    components, imported from <code>@lunarphp/panel</code>.
                </p>
                <div class="mt-2">
                    <StatusBadge tone="sage" size="sm">Add-on</StatusBadge>
                </div>
            </SideCard>

            <PageZone region="main" position="after" />
        </div>
    </div>
</template>
