<script setup>
import { ref } from 'vue'
import Icon from './primitives/Icon.vue'
import FloatingField from './primitives/FloatingField.vue'

const email = ref('')
const news = ref(false)
</script>

<template>
  <section class="block" data-block="contact" style="border-top: 0; padding-top: 8px">
    <div class="block-head">
      <h2 class="block-title">
        <span class="block-step"><span class="num">1</span><span class="chk ico"><Icon name="check" /></span></span>
        Contact information
      </h2>
    </div>
    <div class="stack">
      <FloatingField id="email" v-model="email" label="Email address" type="email" autocomplete="email" inputmode="email" />
      <label class="check">
        <input type="checkbox" v-model="news" />
        <span class="box ico"><Icon name="check" /></span>
        <span class="txt">Email me with order updates and occasional offers.</span>
      </label>
    </div>
  </section>
</template>
