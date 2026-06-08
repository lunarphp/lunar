<script setup>
import { computed, ref } from 'vue'
import Icon from './primitives/Icon.vue'
import { money } from '../utils/money.js'

const props = defineProps({
  order: { type: Object, required: true },
  promoApplied: Boolean,
})

const emit = defineEmits(['apply-promo'])

const promo = ref('')

const currency = computed(() => props.order.currency || 'USD')
const fmt = (minor) => money(minor, currency.value)

const subtotal = computed(() =>
  props.order.items.reduce((sum, i) => sum + i.price * i.qty, 0),
)
const discount = computed(() => props.order.discount?.amount ?? 0)
const total = computed(
  () =>
    subtotal.value +
    (props.order.shipping || 0) +
    (props.order.tax || 0) -
    (props.promoApplied ? discount.value : 0),
)
</script>

<template>
  <div class="co-rail">
    <div class="co-rail-inner">
      <div class="co-merchant">
        <span class="back"><Icon name="arrow-left" :size="20" /></span>
        <span class="mlogo">{{ order.merchant.charAt(0) }}</span>
        <span class="mname">{{ order.merchant }}</span>
      </div>

      <div class="co-amount-block">
        <p class="eyebrow">Pay {{ order.merchant }}</p>
        <h1 class="amount">{{ fmt(total) }}</h1>
        <p class="sub">
          {{ order.items.length }} item{{ order.items.length > 1 ? 's' : '' }} · ships to United States
        </p>
      </div>

      <div class="os">
        <div v-for="(it, idx) in order.items" :key="idx" class="os-item">
          <span class="os-thumb"><Icon :name="it.icon || 'package'" :size="22" /></span>
          <span class="meta">
            <span class="name">{{ it.name }}</span>
            <span class="qty">Qty {{ it.qty }}</span>
          </span>
          <span class="price">{{ fmt(it.price * it.qty) }}</span>
        </div>

        <div class="os-rule" />

        <div v-if="promoApplied" class="os-applied">
          <Icon name="check-circle" :size="15" /> Promo {{ order.discount?.code }} applied
        </div>
        <div v-else class="os-promo">
          <input v-model="promo" placeholder="Promo code" />
          <button @click="emit('apply-promo', promo)">Apply</button>
        </div>

        <div class="os-rule" />

        <div class="os-line"><span>Subtotal</span><span class="v">{{ fmt(subtotal) }}</span></div>
        <div v-if="promoApplied" class="os-line discount">
          <span>Discount ({{ order.discount?.code }})</span><span class="v">−{{ fmt(discount) }}</span>
        </div>
        <div class="os-line"><span>Shipping</span><span class="v">{{ fmt(order.shipping || 0) }}</span></div>
        <div class="os-line"><span>Tax</span><span class="v">{{ fmt(order.tax || 0) }}</span></div>

        <div class="os-rule" />
        <div class="os-total"><span class="l">Total due</span><span class="v">{{ fmt(total) }}</span></div>
      </div>

      <div class="co-rail-foot">
        <div class="co-trust-line">
          <Icon name="lock" :size="14" /> Payments are secured with 256-bit TLS encryption
        </div>
        <div class="co-trust-line">
          <Icon name="shield-check" :size="14" /> Your card details never touch the merchant's servers
        </div>
      </div>
    </div>
  </div>
</template>
