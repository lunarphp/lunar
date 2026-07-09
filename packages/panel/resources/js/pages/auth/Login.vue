<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '../../layouts/AuthLayout.vue';
import Button from '../../components/Button.vue';
import Checkbox from '../../components/Checkbox.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
import TextInput from '../../components/TextInput.vue';
import { useAuthLang } from '../../composables/useLang';

const props = defineProps<{
    urls: { store: string; forgotPassword: string | null };
}>();

const t = useAuthLang();
const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: true,
});

const submit = () => {
    form.post(props.urls.store, {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthLayout>
        <form class="flex flex-col" @submit.prevent="submit">
            <h1 class="text-2xl font-semibold tracking-[-0.02em] text-ink-900">{{ t('sign_in_title') }}</h1>
            <p class="mt-1.5 text-[13px] text-ink-500">{{ t('sign_in_subtitle') }}</p>

            <div class="mt-7 flex flex-col gap-3.5">
                <div>
                    <FieldLabel>{{ t('email') }}</FieldLabel>
                    <TextInput
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        placeholder="you@company.com"
                        :invalid="!!form.errors.email"
                        :aria-label="t('email')"
                    />
                    <div v-if="form.errors.email" class="mt-1 text-[11px] text-danger">{{ form.errors.email }}</div>
                </div>

                <div>
                    <div class="flex items-center gap-1.5 text-xs font-medium text-ink-700 mb-1.5">
                        {{ t('password') }}
                        <Link
                            v-if="urls.forgotPassword"
                            :href="urls.forgotPassword"
                            class="ml-auto text-[11px] font-normal text-ink-400 hover:text-ink-900 transition-colors"
                        >{{ t('forgot_password') }}</Link>
                    </div>
                    <TextInput
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        autocomplete="current-password"
                        :invalid="!!form.errors.password"
                        :aria-label="t('password')"
                    >
                        <template #suffix>
                            <button
                                type="button"
                                class="flex items-center text-ink-500 hover:text-ink-900 transition-colors"
                                :aria-label="showPassword ? t('hide_password') : t('show_password')"
                                @click="showPassword = !showPassword"
                            >
                                <Icon name="eye" />
                            </button>
                        </template>
                    </TextInput>
                    <div v-if="form.errors.password" class="mt-1 text-[11px] text-danger">{{ form.errors.password }}</div>
                </div>

                <label class="mt-1 inline-flex items-center gap-2 text-[12.5px] text-ink-700 select-none cursor-pointer">
                    <Checkbox v-model="form.remember" :aria-label="t('remember')" />
                    {{ t('remember') }}
                </label>
            </div>

            <Button type="submit" variant="primary" class="mt-6 w-full" :disabled="form.processing">
                {{ form.processing ? t('signing_in') : t('continue') }}
            </Button>

            <p class="mt-6 text-[12px] text-ink-400">{{ t('need_access') }}</p>
        </form>
    </AuthLayout>
</template>
