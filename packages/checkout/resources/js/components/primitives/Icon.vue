<script setup>
import { computed } from 'vue'
import * as lucide from 'lucide-vue-next'

const props = defineProps({
  name: { type: String, required: true },
  size: { type: [Number, String], default: 18 },
})

// 'credit-card' -> 'CreditCard'
const pascal = (n) =>
  n
    .split('-')
    .map((p) => p.charAt(0).toUpperCase() + p.slice(1))
    .join('')

const comp = computed(() => lucide[pascal(props.name)] ?? lucide.Circle)
</script>

<template>
  <span class="ico" :style="{ fontSize: Number(size) + 'px' }">
    <component :is="comp" />
  </span>
</template>
