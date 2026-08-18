<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from '../../components/Button.vue';
import CodeInput from '../../components/CodeInput.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
import TextInput from '../../components/TextInput.vue';

const props = defineProps<{
    twoFactorEnabled: boolean;
    pendingTwoFactor: { secret: string; qrCode: string } | null;
    recoveryCodes: string[] | null;
    urls: {
        password: string;
        twoFactor: string;
        twoFactorConfirm: string;
        twoFactorDisable: string;
        recoveryCodes: string;
    };
}>();

const { t } = useI18n();

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitPassword = () => {
    passwordForm.put(props.urls.password, {
        onSuccess: () => passwordForm.reset(),
    });
};

const enrolForm = useForm({});
const beginEnrolment = () => enrolForm.post(props.urls.twoFactor);

const confirmForm = useForm({ code: '' });
const submitConfirm = () => {
    confirmForm.post(props.urls.twoFactorConfirm, {
        onError: () => confirmForm.reset('code'),
    });
};

const regenerateForm = useForm({ password: '' });
const submitRegenerate = () => {
    regenerateForm.post(props.urls.recoveryCodes, {
        onSuccess: () => regenerateForm.reset(),
    });
};

const disableForm = useForm({ password: '' });
const submitDisable = () => {
    disableForm.delete(props.urls.twoFactorDisable, {
        onSuccess: () => disableForm.reset(),
    });
};

const copyCodes = () => {
    if (props.recoveryCodes) {
        navigator.clipboard?.writeText(props.recoveryCodes.join('\n'));
    }
};
</script>

<template>
    <div class="min-h-screen bg-canvas font-sans py-10">
        <div class="mx-auto flex max-w-xl flex-col gap-6 px-6">
            <Head :title="t('auth.security_title')" />
            <h1 class="text-2xl font-semibold tracking-[-0.02em] text-ink-900">{{ t('auth.security_title') }}</h1>

            <!-- Password -->
            <section class="rounded-lg border border-line bg-paper p-6">
                <h2 class="text-[15px] font-semibold text-ink-900">{{ t('auth.password_section_title') }}</h2>
                <p class="mt-1 text-[13px] text-ink-500">{{ t('auth.password_section_subtitle') }}</p>

                <form class="mt-5 flex flex-col gap-3.5" @submit.prevent="submitPassword">
                    <div>
                        <FieldLabel>{{ t('auth.current_password') }}</FieldLabel>
                        <TextInput
                            v-model="passwordForm.current_password"
                            type="password"
                            autocomplete="current-password"
                            :invalid="!!passwordForm.errors.current_password"
                            :aria-label="t('auth.current_password')"
                        />
                        <div v-if="passwordForm.errors.current_password" class="mt-1 text-[11px] text-danger">
                            {{ passwordForm.errors.current_password }}
                        </div>
                    </div>
                    <div>
                        <FieldLabel>{{ t('auth.new_password') }}</FieldLabel>
                        <TextInput
                            v-model="passwordForm.password"
                            type="password"
                            autocomplete="new-password"
                            :invalid="!!passwordForm.errors.password"
                            :aria-label="t('auth.new_password')"
                        />
                        <div v-if="passwordForm.errors.password" class="mt-1 text-[11px] text-danger">
                            {{ passwordForm.errors.password }}
                        </div>
                    </div>
                    <div>
                        <FieldLabel>{{ t('auth.confirm_password') }}</FieldLabel>
                        <TextInput
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            :aria-label="t('auth.confirm_password')"
                        />
                    </div>
                    <div>
                        <Button type="submit" variant="primary" :disabled="passwordForm.processing">
                            {{ t('auth.update_password') }}
                        </Button>
                    </div>
                </form>
            </section>

            <!-- Two-factor -->
            <section class="rounded-lg border border-line bg-paper p-6">
                <div class="flex items-center gap-2">
                    <Icon name="shield" />
                    <h2 class="text-[15px] font-semibold text-ink-900">{{ t('auth.two_factor_title') }}</h2>
                </div>
                <p class="mt-1 text-[13px] text-ink-500">{{ t('auth.two_factor_subtitle') }}</p>

                <!-- Freshly issued recovery codes (shown once) -->
                <div v-if="recoveryCodes" class="mt-5 rounded-md border border-warn-border bg-warn-soft p-4">
                    <div class="flex items-center gap-2 text-[13px] font-medium text-warn-ink">
                        <Icon name="alertTriangle" cls="sm" />
                        {{ t('auth.recovery_codes_title') }}
                    </div>
                    <p class="mt-1 text-[12px] text-warn-ink">{{ t('auth.recovery_codes_intro') }}</p>
                    <div class="mt-3 grid grid-cols-2 gap-1.5 font-mono text-[12px] text-ink-900">
                        <span v-for="code in recoveryCodes" :key="code">{{ code }}</span>
                    </div>
                    <Button class="mt-3" size="sm" icon="copy" @click="copyCodes">{{ t('auth.copy_codes') }}</Button>
                </div>

                <!-- Pending enrolment -->
                <div v-if="pendingTwoFactor" class="mt-5">
                    <p class="text-[13px] text-ink-700">{{ t('auth.two_factor_scan') }}</p>
                    <img :src="pendingTwoFactor.qrCode" alt="" class="mt-3 h-48 w-48 rounded-md border border-line bg-white p-2" />
                    <p class="mt-3 text-[12px] text-ink-500">
                        {{ t('auth.two_factor_manual') }}
                        <span class="font-mono text-ink-700">{{ pendingTwoFactor.secret }}</span>
                    </p>
                    <form class="mt-4 flex flex-col gap-3" @submit.prevent="submitConfirm">
                        <CodeInput
                            v-model="confirmForm.code"
                            :invalid="!!confirmForm.errors.code"
                            :disabled="confirmForm.processing"
                            @complete="submitConfirm"
                        />
                        <div v-if="confirmForm.errors.code" class="text-[11px] text-danger">{{ confirmForm.errors.code }}</div>
                        <div>
                            <Button type="submit" variant="primary" :disabled="confirmForm.processing || confirmForm.code.length !== 6">
                                {{ t('auth.confirm') }}
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- Disabled state -->
                <div v-else-if="!twoFactorEnabled" class="mt-5">
                    <p class="text-[13px] text-ink-500">{{ t('auth.two_factor_disabled_state') }}</p>
                    <Button class="mt-3" variant="primary" :disabled="enrolForm.processing" @click="beginEnrolment">
                        {{ t('auth.enable_two_factor') }}
                    </Button>
                </div>

                <!-- Enabled state -->
                <div v-else class="mt-5 flex flex-col gap-6">
                    <div class="flex items-center gap-2 text-[13px] text-sage-ink">
                        <Icon name="check" cls="sm" />
                        {{ t('auth.two_factor_enabled_state') }}
                    </div>

                    <form class="flex flex-col gap-2" @submit.prevent="submitRegenerate">
                        <FieldLabel :hint="t('auth.password_confirm_hint')">{{ t('auth.regenerate_recovery_codes') }}</FieldLabel>
                        <div class="flex gap-2">
                            <TextInput
                                v-model="regenerateForm.password"
                                type="password"
                                autocomplete="current-password"
                                :invalid="!!regenerateForm.errors.password"
                                :aria-label="t('auth.current_password')"
                            />
                            <Button type="submit" icon="refresh" :disabled="regenerateForm.processing">
                                {{ t('auth.regenerate_recovery_codes') }}
                            </Button>
                        </div>
                        <div v-if="regenerateForm.errors.password" class="text-[11px] text-danger">
                            {{ regenerateForm.errors.password }}
                        </div>
                    </form>

                    <form class="flex flex-col gap-2" @submit.prevent="submitDisable">
                        <FieldLabel :hint="t('auth.password_confirm_hint')">{{ t('auth.disable_two_factor') }}</FieldLabel>
                        <div class="flex gap-2">
                            <TextInput
                                v-model="disableForm.password"
                                type="password"
                                autocomplete="current-password"
                                :invalid="!!disableForm.errors.password"
                                :aria-label="t('auth.current_password')"
                            />
                            <Button type="submit" :disabled="disableForm.processing">
                                {{ t('auth.disable_two_factor') }}
                            </Button>
                        </div>
                        <div v-if="disableForm.errors.password" class="text-[11px] text-danger">
                            {{ disableForm.errors.password }}
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</template>
