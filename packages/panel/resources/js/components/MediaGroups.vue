<script setup lang="ts">
import FileManager from './FileManager.vue';
import MediaManager from './MediaManager.vue';
import type { MediaGroup } from './media';

defineProps<{
    groups: MediaGroup[];
}>();
</script>

<template>
    <template v-for="group in groups" :key="group.collection">
        <MediaManager v-if="group.type === 'image'" :group="group" />
        <FileManager v-else :group="group" />
    </template>
</template>
