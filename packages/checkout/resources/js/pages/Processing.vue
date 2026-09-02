<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import BrandHead from '../components/BrandHead.vue'
import Icon from '../components/primitives/Icon.vue'
import { useCheckoutTheme } from '../composables/useCheckoutTheme.js'

/**
 * Post-confirmation landing (CheckoutController::processing). The server has
 * already tried to settle the session against the gateway before rendering
 * this; landing here means the outcome is still in flight. Poll the same
 * endpoint: each poll re-settles server-side, and the redirect it eventually
 * issues (success URL, or back to the checkout on failure) is followed.
 */
const props = defineProps({
  pollUrl: { type: String, required: true },
  merchant: { type: String, default: 'Store' },
  theme: { type: Object, default: () => ({}) },
  branding: { type: Object, default: () => ({}) },
})

const root = ref(null)
useCheckoutTheme(root, () => props.theme)

// After this many quiet polls, stop hammering the gateway and level with the
// customer; reconciliation finishes the job and the confirmation email lands.
const MAX_POLLS = 24
const slow = ref(false)

let timer = null
let polls = 0

async function poll() {
  try {
    const response = await fetch(props.pollUrl, {
      credentials: 'same-origin',
      redirect: 'follow',
      headers: { Accept: 'text/html' },
    })

    if (response.url && response.url !== props.pollUrl) {
      window.location.assign(response.url)

      return
    }
  } catch {
    // Transient network blip: the next poll covers it.
  }

  polls += 1

  if (polls >= MAX_POLLS) {
    slow.value = true

    return
  }

  timer = setTimeout(poll, 2500)
}

onMounted(() => {
  timer = setTimeout(poll, 2500)
})

onBeforeUnmount(() => clearTimeout(timer))
</script>

<template>
  <div ref="root" class="lunar-checkout">
    <div class="processing-page">
      <BrandHead :merchant="merchant" :logo="branding.logo" :logo-alt="branding.logoAlt" />

      <div class="processing-card" role="status" aria-live="polite">
        <template v-if="!slow">
          <span class="spinner processing-spinner"></span>
          <h1 class="processing-title">Confirming your payment</h1>
          <p class="processing-copy">
            Your payment has been submitted. Hold on a moment while we confirm it with your bank
            and place your order. Do not refresh or close this page just yet.
          </p>
        </template>
        <template v-else>
          <span class="ico processing-slow-ico"><Icon name="clock" :size="26" /></span>
          <h1 class="processing-title">This is taking longer than usual</h1>
          <p class="processing-copy">
            Your payment is still being confirmed. It is safe to close this page: your order will
            be placed automatically once the payment settles, and your confirmation will arrive by
            email. You will not be charged twice.
          </p>
        </template>
      </div>
    </div>
  </div>
</template>
