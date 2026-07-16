<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import PageHeader from '../components/PageHeader.vue';
import PageZone from '../components/PageZone.vue';
import PanelLayout from '../layouts/PanelLayout.vue';

const { t } = useI18n();
</script>

<template>
    <PanelLayout>
        <div data-screen-label="Dashboard" class="contents">
            <PageHeader :title="t('nav.dashboard')" />

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" />

                <div class="rounded-xl border border-line bg-surface p-10 text-center text-[13px] text-ink-500">
                    {{ t('dashboard.widgets_placeholder') }}
                </div>

                <PageZone region="main" position="after" />
            </div>
        </div>
    </PanelLayout>
</template>
