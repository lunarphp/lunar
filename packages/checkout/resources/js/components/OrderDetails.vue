<script setup>
import { computed, ref } from 'vue'
import Icon from './primitives/Icon.vue'
import FloatingField from './primitives/FloatingField.vue'
import { useCheckout } from '../composables/useCheckout.js'

// The server-projected `order-details` element carries the store endpoint and
// whatever was already captured. Without it the section does not render at all.
const props = defineProps({
  element: { type: Object, default: null },
  // Step number, derived by the parent from this element's position among
  // registered main-region elements (spec 0011 §G) — this element is
  // opt-in, so its position in the stack is never fixed.
  step: { type: Number, default: 4 },
})

const { storeElement } = useCheckout()

const captured = computed(() => props.element?.data ?? {})

const reference = ref(captured.value.reference ?? '')
const notes = ref(captured.value.notes ?? '')

const saving = ref(false)
const saved = ref(Boolean(captured.value.reference || captured.value.notes))
const errors = ref({})

let debounceTimer = null

// Persisting only on the button click meant a customer who typed a reference
// and went straight to Pay lost it — `pay()` never flushes the element bag.
// Saving on blur/change (debounced, so tabbing through both fields fires one
// request, not two) means the data is on the session before the customer
// ever reaches the payment button.
function scheduleSave() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(save, 500)
}

// Both fields are optional, so there is nothing to validate client-side and
// nothing to block on. Saving an empty pair is legitimate.
function save() {
  clearTimeout(debounceTimer)

  if (!props.element?.storeUrl || saving.value) return

  storeElement(
    props.element,
    { reference: reference.value, notes: notes.value },
    {
      onStart: () => {
        saving.value = true
        errors.value = {}
      },
      onSuccess: () => {
        saved.value = true
      },
      onError: (err) => {
        errors.value = err
      },
      onFinish: () => {
        saving.value = false
      },
    },
  )
}
</script>

<template>
  <section v-if="element" class="block" data-block="order-details" :class="{ 'is-done': saved }">
    <div class="block-head">
      <h2 class="block-title">
        <span class="block-step"><span class="num">{{ step }}</span><span class="chk ico"><Icon name="check" /></span></span>
        {{ element.title }}
      </h2>
    </div>

    <div class="stack">
      <FloatingField
        id="po-reference"
        v-model="reference"
        label="Purchase order reference"
        autocomplete="off"
        optional
        :error="errors.reference || ''"
        @blur="scheduleSave"
        @change="scheduleSave"
      />

      <div class="fl">
        <textarea
          id="order-notes"
          v-model="notes"
          rows="3"
          maxlength="2000"
          placeholder=" "
          @blur="scheduleSave"
          @change="scheduleSave"
        ></textarea>
        <label for="order-notes">Delivery notes (optional)</label>
      </div>

      <button type="button" class="btn btn-secondary" :disabled="saving" @click="save">
        {{ saving ? 'Saving…' : 'Save order details' }}
      </button>
    </div>
  </section>
</template>
