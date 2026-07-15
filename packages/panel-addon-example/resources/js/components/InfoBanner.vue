<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div style="padding: 1rem; border: 1px solid; margin: 1rem 0">
        {{ message ?? 'Rendered by the example add-on via a panel slot.' }}
    </div>
</template>
