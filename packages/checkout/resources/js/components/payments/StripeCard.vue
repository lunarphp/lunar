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

const { state, registerPaymentConfirm, setPaymentFormError, postJson } = useCheckout()

// One line for every way the form can fail to come up (missing or invalid
// key, intent refused, script blocked, element load error). Stripe's own
// wording names its API and methods, which is for logs, not the customer.
const UNAVAILABLE = 'Card payments are unavailable right now. Please try again in a moment.'

const mountEl = ref(null)
const error = ref('')
const loading = ref(true)

function unavailable(e) {
  if (e) console.error(e)
  error.value = UNAVAILABLE
  loading.value = false
  registerPaymentConfirm(null)
  setPaymentFormError(UNAVAILABLE)
}

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
      postJson(
        state.urls.paymentIntent,
        { payment_method: props.method.handle },
        'Card payment could not be started.',
      ),
      loadStripeJs(),
    ])

    stripe = window.Stripe(key)
    elements = stripe.elements({ clientSecret })
    const paymentElement = elements.create('payment')

    // mount() returns before Stripe has fetched the element; an invalid
    // publishable key surfaces here, not as a thrown error.
    paymentElement.on('loaderror', (event) => unavailable(event?.error))

    // Confirm only becomes possible once the element is on screen. Until
    // then the pay gate refuses with the unavailable copy above.
    paymentElement.on('ready', () => {
      loading.value = false

      registerPaymentConfirm(async () => {
        const result = await stripe.confirmPayment({
          elements,
          confirmParams: { return_url: window.location.href },
          redirect: 'if_required',
        })

        if (result.error) {
          // Card and validation errors are written for the cardholder
          // ("Your card was declined"); anything else is gateway internals.
          const customerFacing = ['card_error', 'validation_error'].includes(result.error.type)

          throw new Error(
            customerFacing ? result.error.message : 'The payment could not be confirmed. Please try again.',
          )
        }
      })
    })

    paymentElement.mount(mountEl.value)
  } catch (e) {
    unavailable(e)
  }
})

onBeforeUnmount(() => {
  registerPaymentConfirm(null)
  setPaymentFormError('')
})
</script>

<template>
  <div class="pm-panel">
    <p v-if="error" class="help" style="color: var(--error-700)">{{ error }}</p>
    <div v-else-if="loading" class="locked">Loading secure payment form…</div>
    <div ref="mountEl"></div>
  </div>
</template>
