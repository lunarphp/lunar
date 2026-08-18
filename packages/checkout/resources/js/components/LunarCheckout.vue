<script setup>
import { computed, ref, watch } from 'vue'
import Icon from './primitives/Icon.vue'
import BrandHead from './BrandHead.vue'
import FulfilmentToggle from './FulfilmentToggle.vue'
import ExpressWallets from './ExpressWallets.vue'
import ContactSection from './ContactSection.vue'
import DeliverySection from './DeliverySection.vue'
import ShippingMethods from './ShippingMethods.vue'
import PaymentSection from './PaymentSection.vue'
import OrderSummary from './OrderSummary.vue'
import SuccessOverlay from './SuccessOverlay.vue'
import { createCheckout } from '../composables/useCheckout.js'
import { useCheckoutTheme } from '../composables/useCheckoutTheme.js'
import { resolveElement } from '../composables/elements.js'

const props = defineProps({
  // CheckoutData payload (placeholder shape until spec 0001 lands).
  checkout: { type: Object, required: true },
  // Validated CheckoutTheme token map ({ '--accent': '#…' }).
  theme: { type: Object, default: () => ({}) },
  // Non-CSS brand assets ({ logo, logoAlt }) from CheckoutTheme::branding().
  branding: { type: Object, default: () => ({}) },
})

const store = createCheckout(props.checkout)
const { state, totalLabel, pay, elementsIn, collectOption } = store

// Partial reloads replace the `checkout` prop wholesale (only: ['checkout']);
// pull the server-owned pieces back into the store.
watch(() => props.checkout, (fresh) => store.sync(fresh), { deep: true })

// Registered elements paired with their resolved component, per region.
// Computed so a chunk that registers its component after first render slots in
// (the registry map is reactive); an unresolved hint is skipped, never a crash.
const resolveRegion = (region) =>
  computed(() =>
    elementsIn(region)
      .map((el) => ({ el, comp: resolveElement(el.component) }))
      .filter((x) => x.comp),
  )

const mainElements = resolveRegion('main')
// The contact region renders through the same registry; when the host hasn't
// registered a contact element server-side, fall back to the presentational
// built-in section so the prototype layout still holds together.
const contactElements = resolveRegion('contact')

const root = ref(null)
useCheckoutTheme(root, () => props.theme)

const mSummaryOpen = ref(false)
</script>

<template>
  <div ref="root" class="lunar-checkout">
    <!-- Mobile · collapsible summary bar -->
    <div class="m-summary-bar">
      <button
        class="m-summary-toggle"
        :aria-expanded="mSummaryOpen"
        @click="mSummaryOpen = !mSummaryOpen"
      >
        <span class="lft"><span class="ico"><Icon name="shopping-bag" :size="18" /></span> Order summary</span>
        <span class="rgt">
          <span class="tot mono">{{ totalLabel }}</span>
          <span class="chev ico"><Icon name="chevron-down" :size="16" /></span>
        </span>
      </button>
      <div v-show="mSummaryOpen" class="m-summary-panel"><OrderSummary /></div>
    </div>

    <div class="page">
      <!-- Left · form -->
      <div class="form-side">
        <div class="form-col">
          <BrandHead :merchant="checkout.merchant" :logo="branding.logo" :logo-alt="branding.logoAlt" />
          <FulfilmentToggle />
          <ExpressWallets />

          <form @submit.prevent="pay">
            <component
              :is="comp"
              v-for="{ el, comp } in contactElements"
              :key="el.handle"
              :element="el"
            />
            <ContactSection v-if="!contactElements.length" />

            <template v-if="state.fulfilment === 'delivery'">
              <DeliverySection />
              <ShippingMethods />
            </template>
            <section v-else class="block">
              <div v-if="collectOption" class="locked">
                <span class="ico"><Icon name="store" :size="17" /></span>
                <span>
                  <strong>{{ collectOption.name }}</strong> — {{ collectOption.sub || 'collect from your local branch at no charge.' }}
                </span>
              </div>
              <div v-else class="locked">
                <span class="ico"><Icon name="store" :size="17" /></span>
                Click &amp; collect isn't available for this order.
              </div>
            </section>

            <!-- Consumer-registered custom elements (main region), placed above
                 payment. Server-projected; rendered via the component registry. -->
            <component
              :is="comp"
              v-for="{ el, comp } in mainElements"
              :key="el.handle"
              :element="el"
            />

            <!-- Static extension point for non-element custom markup. -->
            <slot name="before-payment" />

            <PaymentSection />

            <div class="cta-wrap desktop-cta">
              <button type="submit" class="btn btn-primary btn-block" :disabled="state.processing">
                <span v-if="state.processing" class="spinner"></span>
                <template v-else>
                  <span class="ico"><Icon name="lock" :size="16" /></span>
                  <span class="cta-label">Pay <span class="mono">{{ totalLabel }}</span></span>
                </template>
              </button>
              <p v-if="!state.addressValid && state.fulfilment === 'delivery'" class="cta-hint">
                <span class="ico"><Icon name="arrow-up" :size="15" /></span>
                Complete the steps above to pay — you won't be charged until you confirm.
              </p>
              <p class="legal">
                By paying you agree to our <a href="#">terms</a> and <a href="#">refund policy</a>. You can cancel anytime before dispatch.
              </p>
            </div>

            <div class="foot">
              <a href="#">Refund policy</a><a href="#">Shipping</a><a href="#">Privacy policy</a><a href="#">Terms of service</a><a href="#">Contact</a>
            </div>
            <div class="powered-by">
              <span>Powered by</span>
              <span class="pw">Lunar<span class="dot">.</span></span>
            </div>
          </form>
        </div>
      </div>

      <!-- Right · order summary -->
      <aside class="summary-side" aria-label="Order summary">
        <div class="summary-col">
          <div class="summary-sticky"><OrderSummary /></div>
        </div>
      </aside>
    </div>

    <!-- Mobile · sticky pay bar -->
    <div class="m-pay-bar">
      <button type="button" class="btn btn-primary btn-block" :disabled="state.processing" @click="pay">
        <span v-if="state.processing" class="spinner"></span>
        <template v-else>
          <span class="ico"><Icon name="lock" :size="16" /></span>
          <span class="cta-label">Pay <span class="mono">{{ totalLabel }}</span></span>
        </template>
      </button>
    </div>

    <SuccessOverlay />
  </div>
</template>
