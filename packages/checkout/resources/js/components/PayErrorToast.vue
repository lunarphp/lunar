<script setup>
import Icon from './primitives/Icon.vue'

/**
 * The pay refusal, shown against the sticky pay bar that produced it.
 *
 * Both pay bars are fixed to the bottom of the viewport, so the inline alert
 * further up the page is routinely off screen at the moment the customer
 * presses the CTA: the button simply did nothing as far as they can tell.
 * This is absolutely positioned against its bar, so the reason arrives with
 * the press wherever the page happens to be scrolled.
 */
defineProps({
  message: { type: String, default: '' },
})

defineEmits(['dismiss'])
</script>

<template>
  <Transition name="pay-error">
    <div v-if="message" class="pay-error alert a-error" role="alert" aria-live="assertive">
      <span class="ico"><Icon name="alert-circle" :size="18" /></span>
      <span class="pay-error-text">{{ message }}</span>
      <button type="button" class="pay-error-close" aria-label="Dismiss" @click="$emit('dismiss')">
        <Icon name="x" :size="15" />
      </button>
    </div>
  </Transition>
</template>
