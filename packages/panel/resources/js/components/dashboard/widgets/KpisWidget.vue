<script setup lang="ts">
import KpiCard from '../../KpiCard.vue';
import Sparkline from '../../Sparkline.vue';

type Tone = 'neutral' | 'sage' | 'warn' | 'danger';

interface KpiTile {
    label: string;
    value: string;
    valueExact?: string;
    icon: string;
    tone: Tone;
    delta: { value: string; tone: Tone } | null;
    spark: number[];
}

defineProps<{ data: { tiles: KpiTile[] } }>();
</script>

<template>
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-2.5">
        <KpiCard
            v-for="tile in data.tiles"
            :key="tile.label"
            :label="tile.label"
            :value="tile.value"
            :value-title="tile.valueExact"
            :icon="tile.icon"
            :tone="tile.tone"
            :delta="tile.delta"
        >
            <Sparkline v-if="tile.spark.some((point) => point !== 0)" :points="tile.spark" :width="80" :height="24" />
        </KpiCard>
    </div>
</template>
