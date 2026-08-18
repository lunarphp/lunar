<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthLayout from '../../layouts/AuthLayout.vue';
import Button from '../../components/Button.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import TextInput from '../../components/TextInput.vue';

const props = defineProps<{
    email: string | null;
    token: string;
    urls: { store: string };
}>();

const { t } = useI18n();

const form = useForm({
    token: props.token,
    email: props.email ?? '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(props.urls.store, {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout>
        <form class="flex flex-col" @submit.prevent="submit">
            <Head :title="t('auth.reset_title')" />
            <h1 class="text-2xl font-semibold tracking-[-0.02em] text-ink-900">{{ t('auth.reset_title') }}</h1>
            <p class="mt-1.5 text-[13px] text-ink-500">{{ t('auth.reset_subtitle') }}</p>

            <div class="mt-7 flex flex-col gap-3.5">
                <div>
                    <FieldLabel>{{ t('auth.email') }}</FieldLabel>
                    <TextInput
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        :invalid="!!form.errors.email"
                        :aria-label="t('auth.email')"
                    />
                    <div v-if="form.errors.email" class="mt-1 text-[11px] text-danger">{{ form.errors.email }}</div>
                </div>

                <div>
                    <FieldLabel>{{ t('auth.new_password') }}</FieldLabel>
                    <TextInput
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                        :invalid="!!form.errors.password"
                        :aria-label="t('auth.new_password')"
                    />
                    <div v-if="form.errors.password" class="mt-1 text-[11px] text-danger">{{ form.errors.password }}</div>
                </div>

                <div>
                    <FieldLabel>{{ t('auth.confirm_password') }}</FieldLabel>
                    <TextInput
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        :aria-label="t('auth.confirm_password')"
                    />
                </div>
            </div>

            <Button type="submit" variant="primary" class="mt-6 w-full" :disabled="form.processing">
                {{ form.processing ? t('auth.resetting') : t('auth.reset_button') }}
            </Button>
        </form>
    </AuthLayout>
</template>
