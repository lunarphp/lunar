<script setup>
import { computed } from 'vue'
import Icon from './primitives/Icon.vue'
import Button from './primitives/Button.vue'

const props = defineProps({
  order: { type: Object, required: true },
  totalLabel: { type: String, default: '' },
  last4: { type: String, default: '4242' },
  reference: { type: String, default: '#TND-90412' },
})

defineEmits(['done'])

const date = computed(() =>
  new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
)
</script>

<template>
  <div class="success-wrap">
    <div class="success-card">
      <div class="success-check"><Icon name="check" :size="34" /></div>
      <h1>Payment confirmed</h1>
      <p class="lead">
        Thanks — your payment to <strong>{{ order.merchant }}</strong> went through. A receipt is on
        its way to your email.
      </p>

      <div class="receipt">
        <div class="rrow"><span class="k">Amount paid</span><span class="v mono">{{ totalLabel }}</span></div>
        <div class="rrule" />
        <div class="rrow"><span class="k">Paid to</span><span class="v">{{ order.merchant }}</span></div>
        <div class="rrow"><span class="k">Card</span><span class="v mono">•••• {{ last4 }}</span></div>
        <div class="rrow"><span class="k">Date</span><span class="v">{{ date }}</span></div>
        <div class="rrow"><span class="k">Order</span><span class="v mono">{{ reference }}</span></div>
      </div>

      <Button variant="primary" block @click="$emit('done')">
        Return to {{ order.merchant }} <Icon name="arrow-right" :size="16" />
      </Button>
      <div class="powered" style="margin-top: 22px">Powered by <strong>Lunar</strong></div>
    </div>
  </div>
</template>
