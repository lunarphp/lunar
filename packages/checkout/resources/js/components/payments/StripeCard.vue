<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { useCheckout } from '../../composables/useCheckout.js'

/**
 * First-party Stripe card component (spec 0002 §C). Creates the session's
 * payment intent, mounts a Stripe Payment Element against its client secret,
 * and registers the confirm step the generic Pay action invokes. Completion
 * itself arrives server-side (webhook / reconciliation) — this component
 * never writes success.
 */
const props = defineProps({
  method: { type: Object, required: true },
})

const { state, registerPaymentConfirm, postJson } = useCheckout()

const mountEl = ref(null)
const error = ref('')
const loading = ref(true)

let stripe = null
let elements = null

function loadStripeJs() {
  if (window.Stripe) return Promise.resolve()

  return new Promise((resolve, reject) => {
    const script = document.createElement('script')
    script.src = 'https://js.stripe.com/v3'
    script.onload = resolve
    script.onerror = () => reject(new Error('Could not load the payment form.'))
    document.head.appendChild(script)
  })
}

onMounted(async () => {
  try {
    const key = props.method.config?.publishableKey

    if (!key) throw new Error('Card payments are not configured.')

    const [{ clientSecret }] = await Promise.all([
      postJson(state.urls.paymentIntent, { payment_method: props.method.handle }),
      loadStripeJs(),
    ])

    stripe = window.Stripe(key)
    elements = stripe.elements({ clientSecret })
    elements.create('payment').mount(mountEl.value)

    registerPaymentConfirm(async () => {
      const result = await stripe.confirmPayment({
        elements,
        confirmParams: { return_url: window.location.href },
        redirect: 'if_required',
      })

      if (result.error) {
        throw new Error(result.error.message)
      }
    })
  } catch (e) {
    error.value = e?.message || 'Could not load the payment form.'
  } finally {
    loading.value = false
  }
})

onBeforeUnmount(() => registerPaymentConfirm(null))
</script>

<template>
  <div class="pm-panel">
    <p v-if="error" class="help" style="color: var(--error-700)">{{ error }}</p>
    <div v-else-if="loading" class="locked">Loading secure payment form…</div>
    <div ref="mountEl"></div>
  </div>
</template>
