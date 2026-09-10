<script setup>
import { computed, nextTick, onBeforeUnmount, reactive, ref, watch } from 'vue'
import { router, useHttp } from '@inertiajs/vue3'
import Icon from './primitives/Icon.vue'
import FloatingField from './primitives/FloatingField.vue'
import { useCheckout } from '../composables/useCheckout.js'

// The server-projected `contact` element carries auth state and the endpoints
// (lookupUrl / contactUrl / loginUrl). Without it — the host hasn't registered
// the ContactInformation element — the section stays presentational, as the
// prototype was.
const props = defineProps({
  element: { type: Object, default: null },
})

const p = computed(() => props.element?.props ?? {})
const { state } = useCheckout()
const wired = computed(() => Boolean(props.element))
const signedIn = computed(() => Boolean(p.value.signedIn))

const email = ref(p.value.email ?? '')
const news = ref(false) // presentational — marketing opt-in lands with its own flow

// Guest phases: editing → done. A persisted email round-trips via props.email,
// so a fresh render resumes in `done`.
const phase = ref(!signedIn.value && p.value.email ? 'done' : 'editing')

const lookup = useHttp({ email: '' })
const login = useHttp({ email: '', password: '' })
const logout = useHttp({})

// Two-factor challenge, answered in place. Fortify replies to the login with
// `two_factor: true` instead of a session; the code (or a recovery code) then
// posts to twoFactorUrl and a 204 means the session is signed in. A full page
// visit to the challenge would land the customer wherever the host's login
// redirect points, which is not this checkout.
const twoFactor = useHttp({ code: '', recovery_code: '' })
const twoFactorOpen = ref(false)
const useRecoveryCode = ref(false)
const twoFactorUrl = computed(
  () => p.value.twoFactorUrl || (p.value.loginUrl ? new URL('/two-factor-challenge', p.value.loginUrl).toString() : null),
)
const saving = ref(false)
const signingOut = ref(false)
const confirmingSignOut = ref(false)
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

  // The server refuses "alec@gmail" too (email:rfc,filter); saying so here
  // saves the round trip and keeps the field from ticking green on a typo.
  if (!emailValid.value) {
    fieldError.value = 'Enter a valid email address, like name@example.com.'
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
  resetLogin()
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
    rejectPassword() // 422 → login.errors renders under the (now empty) field
    return
  }
  if (login.hasErrors || !response) {
    rejectPassword()
    return
  }

  if ((response?.data ?? response)?.two_factor) {
    openTwoFactor()
    return
  }

  signedInReload()
}

// Full page load, not an Inertia visit: logging in can merge/swap the cart,
// and show() reconciles to the surviving session (possibly a new UUID).
function signedInReload() {
  window.location.reload()
}

function openTwoFactor() {
  twoFactor.code = ''
  twoFactor.recovery_code = ''
  twoFactor.clearErrors()
  useRecoveryCode.value = false
  twoFactorOpen.value = true
  nextTick(() => document.getElementById('contact-two-factor-code')?.focus())
}

function closeTwoFactor() {
  twoFactorOpen.value = false
  nextTick(() => document.getElementById('contact-password')?.focus())
}

function toggleRecoveryCode() {
  useRecoveryCode.value = !useRecoveryCode.value
  twoFactor.code = ''
  twoFactor.recovery_code = ''
  twoFactor.clearErrors()
  nextTick(() => document.getElementById('contact-two-factor-code')?.focus())
}

const twoFactorValue = computed(() => (useRecoveryCode.value ? twoFactor.recovery_code : twoFactor.code).trim())
const twoFactorError = computed(() => twoFactor.errors.code || twoFactor.errors.recovery_code || '')

async function verifyTwoFactor() {
  if (twoFactor.processing || !twoFactorUrl.value || !twoFactorValue.value) {
    return
  }

  // Fortify checks `code` first and falls back to `recovery_code`; the unused
  // field posts as an empty string, which the host reads as absent.
  if (useRecoveryCode.value) {
    twoFactor.code = ''
  } else {
    twoFactor.recovery_code = ''
  }

  try {
    await twoFactor.post(twoFactorUrl.value, { onHttpException: () => {} })
  } catch {
    return // 422 → twoFactor.errors renders under the field
  }
  if (twoFactor.hasErrors) {
    return
  }

  signedInReload()
}

// A refused password is cleared so the next attempt starts from an empty
// field, the way native sign-in forms behave; the error stays until typing.
function rejectPassword() {
  login.password = ''
  nextTick(() => document.getElementById('contact-password')?.focus())
}

watch(
  () => login.password,
  (value) => {
    if (value && login.hasErrors) {
      login.clearErrors()
    }
  },
)

// Leaving the sign-in path (guest, or coming back to edit later) drops the
// typed password and any refusal, so nothing stale greets the next visit.
function resetLogin() {
  login.password = ''
  login.clearErrors()
}

function change() {
  phase.value = 'editing'
  fieldError.value = ''
  resetLogin()
}

// Signing out invalidates the HTTP session, which orphans the cart this
// checkout was minted from (reloading the same UUID as a guest would 403).
// So leave the checkout entirely and land back on the store. The cart itself
// survives on the account and resurfaces at the next sign-in, but that is
// surprising enough mid-checkout that the button asks first (inline, never a
// blocking browser dialog).
async function signOut() {
  if (signingOut.value || !p.value.logoutUrl) {
    return
  }
  signingOut.value = true

  try {
    await logout.post(p.value.logoutUrl, { onHttpException: () => {} })
  } catch {
    // A failed logout leaves the session signed in; the redirect below just
    // brings the customer back to a checkout they still own.
  }

  window.location.assign(state.urls?.back || '/')
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
      <button
        v-if="wired && signedIn && p.logoutUrl && !confirmingSignOut"
        type="button"
        class="block-action"
        @click="confirmingSignOut = true"
      >
        Sign out
      </button>
    </div>

    <!-- Signed in: the server already associated the customer on render. -->
    <div v-if="wired && signedIn" class="stack">
      <div class="contact-done">
        <span class="ico"><Icon name="user-check" :size="17" /></span>
        <span class="txt">
          Signed in as <strong>{{ p.displayName || p.email }}</strong>
          <span v-if="p.displayName && p.email" class="sub">{{ p.email }}</span>
        </span>
      </div>

      <template v-if="confirmingSignOut">
        <p class="signin-note">
          <span class="ico"><Icon name="log-out" :size="16" /></span>
          <span>
            Signing out ends this checkout. Your basket stays saved to your account and will be
            waiting the next time you sign in.
          </span>
        </p>
        <div class="signin-actions">
          <button type="button" class="btn btn-secondary" :disabled="signingOut" @click="signOut">
            {{ signingOut ? 'Signing out…' : 'Sign out' }}
          </button>
          <button
            type="button"
            class="btn btn-primary"
            :disabled="signingOut"
            @click="confirmingSignOut = false"
          >
            Stay signed in
          </button>
        </div>
      </template>
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
          <span>Welcome back. Sign in for your saved details, or continue as a guest.</span>
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
          class="btn btn-secondary btn-step"
          :disabled="busy || !email"
          @click="continueWithEmail"
        >
          {{ busy ? 'Checking…' : 'Continue' }}
        </button>
      </template>
    </div>

    <!-- Two-factor challenge, in place. Rendered inside the section (not
         teleported): every checkout style is nested under .lunar-checkout, so
         a node moved to <body> loses the kit. Fixed positioning does the
         rest, as SuccessOverlay does. -->
    <div
      v-if="twoFactorOpen"
      class="tf-overlay"
      role="dialog"
      aria-modal="true"
      aria-labelledby="contact-two-factor-title"
      @keydown.esc.prevent="closeTwoFactor"
    >
      <div class="tf-card">
        <div class="tf-icon ico"><Icon name="shield-check" :size="26" /></div>
        <h2 id="contact-two-factor-title">Confirm it's you</h2>
        <p v-if="!useRecoveryCode">Enter the 6-digit code from your authenticator app to finish signing in.</p>
        <p v-else>Enter one of the recovery codes you saved when you set up two-step sign-in.</p>

        <FloatingField
          v-if="!useRecoveryCode"
          id="contact-two-factor-code"
          v-model="twoFactor.code"
          label="Authentication code"
          inputmode="numeric"
          autocomplete="one-time-code"
          maxlength="6"
          mono
          :error="twoFactorError"
          @keydown.enter.prevent="verifyTwoFactor"
        />
        <FloatingField
          v-else
          id="contact-two-factor-code"
          v-model="twoFactor.recovery_code"
          label="Recovery code"
          autocomplete="off"
          mono
          :error="twoFactorError"
          @keydown.enter.prevent="verifyTwoFactor"
        />

        <div class="tf-actions">
          <button type="button" class="btn btn-primary" :disabled="twoFactor.processing || !twoFactorValue" @click="verifyTwoFactor">
            {{ twoFactor.processing ? 'Checking…' : 'Sign in' }}
          </button>
          <button type="button" class="btn btn-secondary" :disabled="twoFactor.processing" @click="closeTwoFactor">Cancel</button>
        </div>

        <button type="button" class="tf-toggle" @click="toggleRecoveryCode">
          {{ useRecoveryCode ? 'Use an authenticator code instead' : 'Lost your device? Use a recovery code' }}
        </button>
      </div>
    </div>
  </section>
</template>
