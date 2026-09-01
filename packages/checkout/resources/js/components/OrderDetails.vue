<script setup>
import { computed, ref } from 'vue'
import { useHttp } from '@inertiajs/vue3'
import Icon from './primitives/Icon.vue'
import FloatingField from './primitives/FloatingField.vue'

// The server-projected `order-details` element carries the store endpoint and
// whatever was already captured. Without it the section does not render at all.
const props = defineProps({
  element: { type: Object, default: null },
})

const p = computed(() => props.element?.props ?? {})
const captured = computed(() => props.element?.data ?? {})

// storeUrl is a sibling of props in the projected element payload
// (projectElements returns handle/title/component/region/props/data/storeUrl),
// not a member of props. Reading it off props gives undefined and the save
// silently does nothing.
const storeUrl = computed(() => props.element?.storeUrl ?? null)

const reference = ref(captured.value.reference ?? '')
const notes = ref(captured.value.notes ?? '')

const form = useHttp({ reference: '', notes: '' })
const saved = ref(Boolean(captured.value.reference || captured.value.notes))

// Both fields are optional, so there is nothing to validate client-side and
// nothing to block on. Saving an empty pair is legitimate.
function save() {
  if (!storeUrl.value || form.processing) return

  form.reference = reference.value
  form.notes = notes.value

  form.post(storeUrl.value, {
    preserveScroll: true,
    onSuccess: () => (saved.value = true),
  })
}
</script>

<template>
  <section v-if="element" class="block" data-block="order-details" :class="{ 'is-done': saved }">
    <div class="block-head">
      <h2 class="block-title">
        <span class="block-step"><span class="num">4</span><span class="chk ico"><Icon name="check" /></span></span>
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
        :error="form.errors.reference || ''"
      />

      <div class="fl">
        <textarea id="order-notes" v-model="notes" rows="3" maxlength="2000" placeholder=" "></textarea>
        <label for="order-notes">Delivery notes (optional)</label>
      </div>

      <button type="button" class="btn btn-secondary" :disabled="form.processing" @click="save">
        {{ form.processing ? 'Saving…' : 'Save order details' }}
      </button>
    </div>
  </section>
</template>
