<script setup>
import { computed, onBeforeUnmount, ref } from 'vue'
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

const { state, storeElement, registerPendingWrite } = useCheckout()

// One notes field whatever the fulfilment: it lands on the order as order
// notes either way. The hint says what is useful to write for this mode.
const notesHint = computed(() =>
  state.fulfilment === 'collect'
    ? 'Anything the branch should know before you arrive.'
    : 'Access, gate codes, where to leave it, who to ask for.',
)

const captured = computed(() => props.element?.data ?? {})

const reference = ref(captured.value.reference ?? '')
const notes = ref(captured.value.notes ?? '')

const saving = ref(false)
const saved = ref(Boolean(captured.value.reference || captured.value.notes))
const errors = ref({})

let debounceTimer = null
// The in-flight save's promise, held so `flush` can await a request that's
// already been sent (debounce already fired) rather than treating it as
// nothing-pending because the timer itself is clear.
let inFlight = null

// Persisting only on the button click meant a customer who typed a reference
// and went straight to Pay lost it. Saving on blur/change (debounced, so
// tabbing through both fields fires one request, not two) means the data is
// on the session before the customer ever reaches the payment button, and
// `flush` below covers the remaining case: Pay clicked inside the debounce
// window, or while the debounced save is still in flight.
function scheduleSave() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    debounceTimer = null
    save()
  }, 500)
}

// Both fields are optional, so there is nothing to validate client-side and
// nothing to block on. Saving an empty pair is legitimate. Always returns a
// promise so `flush` has something to await: the new request, the request
// already in flight (never fires a duplicate), or a resolved no-op.
function save() {
  if (debounceTimer !== null) {
    clearTimeout(debounceTimer)
    debounceTimer = null
  }

  if (!props.element?.storeUrl) return Promise.resolve()
  if (saving.value) return inFlight ?? Promise.resolve()

  inFlight = storeElement(
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
        inFlight = null
      },
    },
  )

  return inFlight
}

// Registered with the checkout store so `pay()` can await this element's
// write landing before it posts to the pay boundary. A cheap no-op when
// there's no scheduled save and nothing in flight.
function flush() {
  if (debounceTimer === null && !saving.value) return Promise.resolve()

  return save()
}

const unregisterPendingWrite = registerPendingWrite(flush)
onBeforeUnmount(unregisterPendingWrite)
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
        <label for="order-notes">Order notes (optional)</label>
      </div>
      <p class="help" style="margin-top: -6px">{{ notesHint }}</p>

      <button type="button" class="btn btn-secondary btn-step" :disabled="saving" @click="save">
        {{ saving ? 'Saving…' : 'Save order details' }}
      </button>
    </div>
  </section>
</template>
