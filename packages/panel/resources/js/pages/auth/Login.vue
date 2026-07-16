<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthLayout from '../../layouts/AuthLayout.vue';
import Button from '../../components/Button.vue';
import Checkbox from '../../components/Checkbox.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
import TextInput from '../../components/TextInput.vue';

const props = defineProps<{
    urls: { store: string; forgotPassword: string | null };
}>();

// Dev: swap this URL for your own brand asset. AuthLayout falls back to a
// soft sage gradient if the image fails to load or this is left empty.
const HERO_IMAGE = 'https://images.unsplash.com/photo-1761090617068-f1b3257d27ad?w=1600&q=80';
const HERO_ALT = 'Curated clothing racks in a boutique';

const { t } = useI18n();
const showPassword = ref(false);
const emailInputRef = ref<InstanceType<typeof TextInput> | null>(null);

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

onMounted(() => {
    emailInputRef.value?.focus();
});
</script>

<template>
    <AuthLayout :image="HERO_IMAGE" :image-alt="HERO_ALT">
        <template #caption>
            <div class="font-medium text-white">Built for the next chapter of commerce.</div>
            <div class="mt-1 text-white/65">Lunar v2</div>
        </template>

        <form class="flex flex-col" @submit.prevent="submit">
            <Head :title="t('auth.sign_in_title')" />
            <h1 class="text-2xl font-semibold tracking-[-0.02em] text-ink-900">{{ t('auth.sign_in_title') }}</h1>
            <p class="mt-1.5 text-[13px] text-ink-500">{{ t('auth.sign_in_subtitle') }}</p>

            <div class="mt-7 flex flex-col gap-3.5">
                <div>
                    <FieldLabel>{{ t('auth.email') }}</FieldLabel>
                    <TextInput
                        ref="emailInputRef"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        placeholder="you@company.com"
                        :invalid="!!form.errors.email"
                        :aria-label="t('auth.email')"
                    />
                    <div v-if="form.errors.email" class="mt-1 text-[11px] text-danger">{{ form.errors.email }}</div>
                </div>

                <div>
                    <div class="flex items-center gap-1.5 text-xs font-medium text-ink-700 mb-1.5">
                        {{ t('auth.password') }}
                        <Link
                            v-if="urls.forgotPassword"
                            :href="urls.forgotPassword"
                            tabindex="-1"
                            class="ml-auto text-[11px] font-normal text-ink-400 hover:text-ink-900 transition-colors"
                        >{{ t('auth.forgot_password') }}</Link>
                    </div>
                    <TextInput
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        autocomplete="current-password"
                        :invalid="!!form.errors.password"
                        :aria-label="t('auth.password')"
                    >
                        <template #suffix>
                            <button
                                type="button"
                                class="flex items-center text-ink-500 hover:text-ink-900 transition-colors"
                                :aria-label="showPassword ? t('auth.hide_password') : t('auth.show_password')"
                                @click="showPassword = !showPassword"
                            >
                                <Icon :name="showPassword ? 'eyeOff' : 'eye'" />
                            </button>
                        </template>
                    </TextInput>
                    <div v-if="form.errors.password" class="mt-1 text-[11px] text-danger">{{ form.errors.password }}</div>
                </div>

                <label class="mt-1 inline-flex items-center gap-2 text-[12.5px] text-ink-700 select-none cursor-pointer">
                    <Checkbox v-model="form.remember" :aria-label="t('auth.remember')" />
                    {{ t('auth.remember') }}
                </label>
            </div>

            <Button type="submit" variant="primary" class="mt-6 w-full" :disabled="form.processing">
                <Icon v-if="!form.processing" name="arrowLeft" cls="rotate-180" />
                {{ form.processing ? t('auth.signing_in') : t('auth.continue') }}
            </Button>

            <p class="mt-6 text-[12px] text-ink-400">{{ t('auth.need_access') }}</p>
        </form>
    </AuthLayout>
</template>
