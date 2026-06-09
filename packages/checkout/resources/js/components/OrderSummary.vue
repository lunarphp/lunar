<script setup>
import { ref } from 'vue'
import Icon from './primitives/Icon.vue'
import { useCheckout } from '../composables/useCheckout.js'

const { state, fmt, itemCount, breakdown, applyDiscount, removeDiscount } = useCheckout()

const code = ref('')
const onApply = () => {
  applyDiscount(code.value)
  if (state.discount) code.value = ''
}
</script>

<template>
  <div id="summary-card">
    <div class="os-h">
      <h2>Order summary</h2>
      <span class="count">{{ itemCount }} item{{ itemCount === 1 ? '' : 's' }}</span>
    </div>

    <ul class="os-items" :class="{ 'is-scroll': state.items.length > 5 }">
      <li v-for="it in state.items" :key="it.id" class="os-item">
        <span class="os-thumb">
          <Icon :name="it.icon || 'package'" :size="22" />
          <span class="qty">{{ it.qty }}</span>
        </span>
        <span class="meta">
          <div class="iname">{{ it.title }}</div>
          <div class="ivar">{{ it.variant }}</div>
          <div v-if="it.sku" class="isku">{{ it.sku }}</div>
        </span>
        <span class="iprice">{{ fmt(it.price * it.qty) }}</span>
      </li>
    </ul>

    <div class="os-rule"></div>

    <!-- Discount -->
    <div v-if="state.discount" class="disc-applied">
      <span class="tag"><span class="ico"><Icon name="badge-check" :size="15" /></span> {{ state.discount.code }} · {{ state.discount.label }}</span>
      <button type="button" class="remove" @click="removeDiscount">Remove</button>
    </div>
    <div v-else>
      <div class="disc-form">
        <input
          v-model="code"
          class="disc-input"
          :class="{ 'is-err': state.discountError }"
          placeholder="Discount code"
          @keyup.enter="onApply"
        />
        <button type="button" class="disc-apply-btn" @click="onApply">Apply</button>
      </div>
      <p v-if="state.discountError" class="help" style="color: var(--error-700)">{{ state.discountError }}</p>
    </div>

    <div class="os-rule"></div>

    <div class="os-lines">
      <div class="os-line subtotal"><span>Subtotal</span><span class="v">{{ fmt(breakdown.subtotal) }}</span></div>
      <div v-if="breakdown.discGoods" class="os-line discount">
        <span>Discount</span><span class="v">−{{ fmt(breakdown.discGoods) }}</span>
      </div>
      <div class="os-line">
        <span>{{ state.fulfilment === 'collect' ? 'Collection' : 'Shipping' }}</span>
        <span class="ship-v">
          <span v-if="breakdown.discShip" class="v strike">{{ fmt(breakdown.baseShipping) }}</span>
          <span class="v">{{ breakdown.shipping ? fmt(breakdown.shipping) : 'Free' }}</span>
        </span>
      </div>
      <div class="os-line tax"><span>VAT (incl.)</span><span class="v">{{ fmt(breakdown.vat) }}</span></div>
    </div>

    <div class="os-total">
      <span class="tl">Total</span>
      <span class="tv"><span class="cur">{{ state.currency }}</span><span class="amt">{{ fmt(breakdown.total) }}</span></span>
    </div>

    <div class="os-trust">
      <div class="line"><span class="ico"><Icon name="lock" :size="15" /></span> Secure, encrypted payment</div>
      <div class="line"><span class="ico"><Icon name="shield-check" :size="15" /></span> Buyer protection on every order</div>
    </div>
  </div>
</template>
