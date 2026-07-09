<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '../../layouts/AuthLayout.vue';
import Button from '../../components/Button.vue';
import CodeInput from '../../components/CodeInput.vue';
import Icon from '../../components/Icon.vue';
import TextInput from '../../components/TextInput.vue';
import { useAuthLang } from '../../composables/useLang';

const props = defineProps<{
    urls: { store: string; login: string };
}>();

const t = useAuthLang();
const useRecovery = ref(false);
const codeInputRef = ref<InstanceType<typeof CodeInput> | null>(null);

const form = useForm({
    code: '',
    recovery_code: '',
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
</script>

<template>
    <AuthLayout>
        <form class="flex flex-col" @submit.prevent="submit">
            <Link
                :href="urls.login"
                class="-ml-1 mb-5 inline-flex items-center gap-1 self-start text-[12px] text-ink-500 hover:text-ink-900 transition-colors"
            >
                <Icon name="arrowLeft" cls="sm" />
                {{ t('use_different_account') }}
            </Link>

            <div class="inline-flex items-center justify-center w-9 h-9 rounded-md bg-sage-soft text-sage-ink mb-4">
                <Icon name="shield" />
            </div>

            <h1 class="text-2xl font-semibold tracking-[-0.02em] text-ink-900">{{ t('challenge_title') }}</h1>
            <p class="mt-1.5 text-[13px] text-ink-500">
                {{ useRecovery ? t('challenge_recovery_subtitle') : t('challenge_subtitle') }}
            </p>

            <div class="mt-7">
                <template v-if="useRecovery">
                    <TextInput
                        v-model="form.recovery_code"
                        mono
                        autocomplete="off"
                        :invalid="!!form.errors.recovery_code"
                        :aria-label="t('recovery_code')"
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
                type="submit"
                variant="primary"
                class="mt-6 w-full"
                :disabled="form.processing || (!useRecovery && form.code.length !== 6) || (useRecovery && !form.recovery_code)"
            >
                {{ form.processing ? t('verifying') : t('verify_button') }}
            </Button>

            <div class="mt-5 text-[12px]">
                <button
                    type="button"
                    class="text-ink-700 hover:text-ink-900 underline-offset-4 hover:underline transition-colors"
                    @click="toggleRecovery"
                >
                    {{ useRecovery ? t('use_authenticator') : t('use_recovery_code') }}
                </button>
            </div>
        </form>
    </AuthLayout>
</template>
