<script setup lang="ts">
// Plain Vue component only — no `@inertiajs/vue3` composable imports here.
// The add-on's IIFE bundle can't resolve that import (it isn't published as a
// window global), but Inertia still passes page props to whatever component
// it resolves, so `defineProps` is all that's needed.
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div style="padding: 2rem">
        <h1>Example Add-on Page</h1>
        <p>{{ message ?? 'This page is served by a separately-compiled add-on package.' }}</p>
    </div>
</template>
