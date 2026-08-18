<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthLayout from '../../layouts/AuthLayout.vue';
import Button from '../../components/Button.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
import TextInput from '../../components/TextInput.vue';

const props = defineProps<{
    urls: { store: string; login: string };
}>();

const { t } = useI18n();

const flashSuccess = computed(() => (usePage().props.flash as { success?: string })?.success);

const form = useForm({ email: '' });

const submit = () => form.post(props.urls.store);
</script>

<template>
    <AuthLayout>
        <form class="flex flex-col" @submit.prevent="submit">
            <Link
                :href="urls.login"
                class="-ml-1 mb-5 inline-flex items-center gap-1 self-start text-[12px] text-ink-500 hover:text-ink-900 transition-colors"
            >
                <Icon name="arrowLeft" cls="sm" />
                {{ t('auth.back_to_sign_in') }}
            </Link>

            <Head :title="t('auth.forgot_title')" />
            <h1 class="text-2xl font-semibold tracking-[-0.02em] text-ink-900">{{ t('auth.forgot_title') }}</h1>
            <p class="mt-1.5 text-[13px] text-ink-500">{{ t('auth.forgot_subtitle') }}</p>

            <!-- Persistent inline note rather than a toast: "check your email"
                 must stay readable for as long as the page is open. -->
            <div
                v-if="flashSuccess"
                role="status"
                class="mt-5 flex items-center gap-2 rounded-md border border-sage-border bg-sage-soft px-3 py-2 text-[12px] text-sage-ink"
            >
                <Icon name="check" cls="sm" class="shrink-0" />
                <span class="flex-1 min-w-0">{{ flashSuccess }}</span>
            </div>

            <div class="mt-7">
                <FieldLabel>{{ t('auth.email') }}</FieldLabel>
                <TextInput
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    placeholder="you@company.com"
                    :invalid="!!form.errors.email"
                    :aria-label="t('auth.email')"
                />
                <div v-if="form.errors.email" class="mt-1 text-[11px] text-danger">{{ form.errors.email }}</div>
            </div>

            <Button type="submit" variant="primary" class="mt-6 w-full" :disabled="form.processing">
                {{ form.processing ? t('auth.sending') : t('auth.send_reset_link') }}
            </Button>
        </form>
    </AuthLayout>
</template>
