<script setup>
import { computed, nextTick, onBeforeUnmount, reactive, ref, watch } from 'vue'
import { router, useHttp } from '@inertiajs/vue3'
import Icon from './primitives/Icon.vue'
import FloatingField from './primitives/FloatingField.vue'

// The server-projected `contact` element carries auth state and the endpoints
// (lookupUrl / contactUrl / loginUrl). Without it — the host hasn't registered
// the ContactInformation element — the section stays presentational, as the
// prototype was.
const props = defineProps({
  element: { type: Object, default: null },
})

const p = computed(() => props.element?.props ?? {})
const wired = computed(() => Boolean(props.element))
const signedIn = computed(() => Boolean(p.value.signedIn))

const email = ref(p.value.email ?? '')
const news = ref(false) // presentational — marketing opt-in lands with its own flow

// Guest phases: editing → done. A persisted email round-trips via props.email,
// so a fresh render resumes in `done`.
const phase = ref(!signedIn.value && p.value.email ? 'done' : 'editing')

const lookup = useHttp({ email: '' })
const login = useHttp({ email: '', password: '' })
const saving = ref(false)
const fieldError = ref('')

const done = computed(() => signedIn.value || phase.value === 'done')
const busy = computed(() => lookup.processing || login.processing || saving.value)

// --- Account detection -----------------------------------------------------
//
// As soon as the input holds a plausible email, look it up (debounced) and, if
// it belongs to an account, reveal the password field in place — no Continue
// click needed. The endpoint validates `email` server-side on the same request
// that answers { exists }, so no separate precognitive round-trip is spent on
// a throttled route. Results are cached per address; a throttled or failed
// lookup just leaves the manual Continue path in charge.
const known = reactive(new Map())
const candidate = computed(() => email.value.trim())
const emailValid = computed(() => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(candidate.value))
const canSignIn = computed(() => known.get(candidate.value) === true && Boolean(p.value.loginUrl))

let debounceTimer = null

watch(email, () => {
  fieldError.value = ''
  clearTimeout(debounceTimer)

  if (!wired.value || signedIn.value || !emailValid.value || known.has(candidate.value)) {
    return
  }

  debounceTimer = setTimeout(() => detectAccount(candidate.value), 450)
})

onBeforeUnmount(() => clearTimeout(debounceTimer))

async function detectAccount(address) {
  if (address !== candidate.value) {
    return // input moved on while debouncing
  }

  const exists = await lookupExists(address, { quiet: true })

  if (exists === null) {
    return // unknown (throttled/invalid) — Continue still resolves it manually
  }

  known.set(address, exists)

  if (exists && address === candidate.value) {
    login.password = ''
    nextTick(() => document.getElementById('contact-password')?.focus())
  }
}

/**
 * POST the lookup and normalise the outcome: true/false when the server
 * answered, null when it couldn't (validation error, throttle, network).
 */
async function lookupExists(address, { quiet = false } = {}) {
  lookup.email = address

  try {
    const response = await lookup.post(p.value.lookupUrl, { onHttpException: () => {} })

    if (lookup.hasErrors) {
      if (!quiet) {
        fieldError.value = lookup.errors.email ?? ''
      }
      return null
    }

    return response ? Boolean((response?.data ?? response)?.exists) : null
  } catch {
    return null
  }
}

// --- Actions -----------------------------------------------------------------

async function continueWithEmail() {
  if (!wired.value || busy.value || !candidate.value) {
    return
  }

  if (canSignIn.value) {
    document.getElementById('contact-password')?.focus()
    return
  }

  if (known.get(candidate.value) === false) {
    persistGuest()
    return
  }

  fieldError.value = ''
  const exists = await lookupExists(candidate.value)

  if (exists === null) {
    if (lookup.hasErrors) {
      return // server rejected the address — error is showing
    }
    // Throttled/unreachable: don't cache, don't block — take the guest path.
    persistGuest()
    return
  }

  known.set(candidate.value, exists)

  if (exists && p.value.loginUrl) {
    login.password = ''
    nextTick(() => document.getElementById('contact-password')?.focus())
    return // password field revealed via canSignIn
  }

  persistGuest()
}

function persistGuest() {
  saving.value = true
  router.post(
    p.value.contactUrl,
    { email: candidate.value },
    {
      preserveScroll: true,
      preserveState: true,
      only: ['checkout'],
      onSuccess: () => {
        phase.value = 'done'
      },
      onError: (errors) => {
        phase.value = 'editing'
        fieldError.value = errors.email ?? ''
      },
      onFinish: () => {
        saving.value = false
      },
    },
  )
}

async function signIn() {
  if (busy.value || !login.password) {
    return
  }
  login.email = candidate.value

  let response = null
  try {
    response = await login.post(p.value.loginUrl, { onHttpException: () => {} })
  } catch {
    return // 422 → login.errors renders under the password field
  }
  if (login.hasErrors || !response) {
    return
  }

  // Fortify can answer with a 2FA challenge instead of a session; that page
  // lives on the host app at Fortify's default path.
  if ((response?.data ?? response)?.two_factor) {
    window.location.assign(new URL('/two-factor-challenge', p.value.loginUrl))
    return
  }

  // Full page load, not an Inertia visit: logging in can merge/swap the cart,
  // and show() reconciles to the surviving session (possibly a new UUID).
  window.location.reload()
}

function change() {
  phase.value = 'editing'
  fieldError.value = ''
}
</script>

<template>
  <section class="block" data-block="contact" :class="{ 'is-done': done }" style="border-top: 0; padding-top: 8px">
    <div class="block-head">
      <h2 class="block-title">
        <span class="block-step"><span class="num">1</span><span class="chk ico"><Icon name="check" /></span></span>
        Contact information
      </h2>
      <button v-if="wired && done && !signedIn" type="button" class="block-action" @click="change">Change</button>
    </div>

    <!-- Signed in: the server already associated the customer on render. -->
    <div v-if="wired && signedIn" class="contact-done">
      <span class="ico"><Icon name="user-check" :size="17" /></span>
      <span class="txt">
        Signed in as <strong>{{ p.displayName || p.email }}</strong>
        <span v-if="p.displayName && p.email" class="sub">{{ p.email }}</span>
      </span>
    </div>

    <!-- Guest email persisted onto the session. -->
    <div v-else-if="wired && phase === 'done'" class="contact-done">
      <span class="ico"><Icon name="mail-check" :size="17" /></span>
      <span class="txt">Order updates go to <strong>{{ email }}</strong></span>
    </div>

    <!-- Editing (wired) / presentational fallback (unwired). The password
         reveals itself in place once the address is known to have an account. -->
    <div v-else class="stack">
      <FloatingField
        id="email"
        v-model="email"
        label="Email address"
        type="email"
        autocomplete="email"
        inputmode="email"
        :error="fieldError"
        @keydown.enter.prevent="continueWithEmail"
      />

      <template v-if="canSignIn">
        <p class="signin-note">
          <span class="ico"><Icon name="circle-user-round" :size="16" /></span>
          <span>Welcome back — sign in for your saved details, or continue as a guest.</span>
        </p>
        <FloatingField
          id="contact-password"
          v-model="login.password"
          label="Password"
          type="password"
          autocomplete="current-password"
          :error="login.errors.email || login.errors.password || ''"
          @keydown.enter.prevent="signIn"
        />
        <div class="signin-actions">
          <button type="button" class="btn btn-primary" :disabled="busy || !login.password" @click="signIn">
            {{ login.processing ? 'Signing in…' : 'Sign in' }}
          </button>
          <button type="button" class="btn btn-secondary" :disabled="busy" @click="persistGuest">
            {{ saving ? 'One moment…' : 'Continue as guest' }}
          </button>
        </div>
      </template>

      <template v-else>
        <label class="check">
          <input type="checkbox" v-model="news" />
          <span class="box ico"><Icon name="check" /></span>
          <span class="txt">Email me with order updates and offers.</span>
        </label>
        <button
          v-if="wired"
          type="button"
          class="btn btn-secondary contact-continue"
          :disabled="busy || !email"
          @click="continueWithEmail"
        >
          {{ busy ? 'Checking…' : 'Continue' }}
        </button>
      </template>
    </div>
  </section>
</template>
