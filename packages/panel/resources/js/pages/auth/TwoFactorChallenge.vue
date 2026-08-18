<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthLayout from '../../layouts/AuthLayout.vue';
import Button from '../../components/Button.vue';
import CodeInput from '../../components/CodeInput.vue';
import Icon from '../../components/Icon.vue';
import TextInput from '../../components/TextInput.vue';

const props = defineProps<{
    method: 'authenticator' | 'email';
    obfuscatedEmail?: string | null;
    cooldownRemaining?: number;
    urls: { store: string; resend: string; login: string };
}>();

// Dev: swap this URL for your own brand asset. AuthLayout falls back to a
// soft sage gradient if the image fails to load or this is left empty.
const HERO_IMAGE = 'https://images.unsplash.com/photo-1761090617068-f1b3257d27ad?w=1600&q=80';
const HERO_ALT = 'Curated clothing racks in a boutique';

const { t } = useI18n();
const useRecovery = ref(false);
const codeInputRef = ref<InstanceType<typeof CodeInput> | null>(null);

const form = useForm({
    code: '',
    recovery_code: '',
});

// Sends no payload, but the server may return a `code` validation error (e.g. resend cooldown).
const resendForm = useForm<{ code?: string }>({});

const resendCooldown = ref(props.cooldownRemaining ?? 0);
let cooldownTimer: ReturnType<typeof setInterval> | null = null;

const startCooldown = (seconds: number) => {
    resendCooldown.value = seconds;

    if (cooldownTimer) {
        clearInterval(cooldownTimer);
    }

    cooldownTimer = setInterval(() => {
        resendCooldown.value -= 1;

        if (resendCooldown.value <= 0) {
            resendCooldown.value = 0;

            if (cooldownTimer) {
                clearInterval(cooldownTimer);
                cooldownTimer = null;
            }
        }
    }, 1000);
};

if (props.method === 'email' && resendCooldown.value > 0) {
    startCooldown(resendCooldown.value);
}

// A resend reloads the challenge page with a fresh cooldownRemaining prop —
// restart the local countdown from that server-supplied value.
watch(
    () => props.cooldownRemaining,
    (seconds) => {
        if (props.method === 'email' && typeof seconds === 'number' && seconds > 0) {
            startCooldown(seconds);
        }
    },
);

onBeforeUnmount(() => {
    if (cooldownTimer) {
        clearInterval(cooldownTimer);
    }
});

const submit = () => {
    form.post(props.urls.store, {
        onError: () => {
            if (!useRecovery.value) {
                form.code = '';
                codeInputRef.value?.clear();
            }
        },
    });
};

const toggleRecovery = () => {
    useRecovery.value = !useRecovery.value;
    form.reset();
    form.clearErrors();
};

const resend = () => {
    if (resendCooldown.value > 0 || resendForm.processing) {
        return;
    }

    resendForm.post(props.urls.resend, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AuthLayout :image="HERO_IMAGE" :image-alt="HERO_ALT">
        <template #caption>
            <div class="font-medium text-white">Built for the next chapter of commerce.</div>
            <div class="mt-1 text-white/65">Lunar v2</div>
        </template>

        <form class="flex flex-col" @submit.prevent="submit">
            <Link
                :href="urls.login"
                class="-ml-1 mb-5 inline-flex items-center gap-1 self-start text-[12px] text-ink-500 hover:text-ink-900 transition-colors"
            >
                <Icon name="arrowLeft" cls="sm" />
                {{ t('auth.use_different_account') }}
            </Link>

            <div class="inline-flex items-center justify-center w-9 h-9 rounded-md bg-sage-soft text-sage-ink mb-4">
                <Icon :name="method === 'email' ? 'mail' : 'shield'" />
            </div>

            <Head :title="t('auth.challenge_title')" />
            <h1 class="text-2xl font-semibold tracking-[-0.02em] text-ink-900">{{ t('auth.challenge_title') }}</h1>
            <p class="mt-1.5 text-[13px] text-ink-500">
                <template v-if="useRecovery">{{ t('auth.challenge_recovery_subtitle') }}</template>
                <template v-else-if="method === 'email'">
                    {{ t('auth.challenge_email_subtitle', { email: obfuscatedEmail }) }}
                </template>
                <template v-else>{{ t('auth.challenge_subtitle') }}</template>
            </p>

            <div class="mt-7">
                <template v-if="useRecovery">
                    <TextInput
                        v-model="form.recovery_code"
                        mono
                        autocomplete="off"
                        :invalid="!!form.errors.recovery_code"
                        :aria-label="t('auth.recovery_code')"
                        placeholder="xxxxxxxxxx-xxxxxxxxxx"
                    />
                    <div v-if="form.errors.recovery_code" class="mt-2 text-[11px] text-danger">{{ form.errors.recovery_code }}</div>
                </template>
                <template v-else>
                    <CodeInput
                        ref="codeInputRef"
                        v-model="form.code"
                        :invalid="!!form.errors.code"
                        auto-focus
                        :disabled="form.processing"
                        @complete="submit"
                    />
                    <div v-if="form.errors.code" class="mt-2 text-[11px] text-danger">{{ form.errors.code }}</div>
                </template>
            </div>

            <Button
                v-if="useRecovery"
                type="submit"
                variant="primary"
                class="mt-6 w-full"
                :disabled="form.processing || !form.recovery_code"
            >
                {{ form.processing ? t('auth.verifying') : t('auth.verify_button') }}
            </Button>

            <!-- CodeInput auto-submits on the 6th digit (see @complete above), so there's
            nothing to click here — just a status while the request is in flight. -->
            <div v-else-if="form.processing" class="mt-6 flex items-center justify-center gap-2 text-[13px] text-ink-500">
                <span class="h-3.5 w-3.5 shrink-0 rounded-full border-2 border-ink-300 border-t-transparent animate-spin" />
                {{ t('auth.verifying') }}
            </div>

            <div v-if="method === 'email'" class="mt-5 flex items-center gap-3 text-[12px]">
                <button
                    type="button"
                    class="text-ink-500 disabled:cursor-not-allowed hover:text-ink-900 transition-colors"
                    :disabled="resendCooldown > 0 || resendForm.processing"
                    @click="resend"
                >
                    <span v-if="resendCooldown > 0">{{ t('auth.resend_in_seconds', { seconds: resendCooldown }) }}</span>
                    <span v-else>{{ t('auth.resend_code') }}</span>
                </button>
                <span v-if="resendForm.recentlySuccessful" class="text-sage-ink">{{ t('auth.code_resent') }}</span>
            </div>
            <div v-if="method === 'email' && resendForm.errors.code" class="mt-2 text-[11px] text-danger">
                {{ resendForm.errors.code }}
            </div>

            <div v-if="method === 'authenticator'" class="mt-5 text-[12px]">
                <button
                    type="button"
                    class="text-ink-700 hover:text-ink-900 underline-offset-4 hover:underline transition-colors"
                    @click="toggleRecovery"
                >
                    {{ useRecovery ? t('auth.use_authenticator') : t('auth.use_recovery_code') }}
                </button>
            </div>
        </form>
    </AuthLayout>
</template>
