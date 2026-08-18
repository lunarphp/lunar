<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { type BreadcrumbItem } from '../../../components/Breadcrumbs.vue';
import Button from '../../../components/Button.vue';
import ConfirmDialog from '../../../components/ConfirmDialog.vue';
import DataTable from '../../../components/DataTable.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import PageEmpty from '../../../components/PageEmpty.vue';
import Section from '../../../components/Section.vue';
import TextInput from '../../../components/TextInput.vue';
import Tooltip from '../../../components/Tooltip.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type Country = {
    id: number;
    name: string;
    iso2: string | null;
    iso3: string;
    phonecode: string;
    emoji: string;
};

type CountryState = {
    id: number;
    name: string;
    code: string;
    inTaxZone: boolean;
    urls: { destroy: string };
};

const props = defineProps<{
    country: Country;
    states: CountryState[];
    urls: { update: string; destroy: string; index: string; storeState: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.settings') },
    { label: t('countries.title'), href: props.urls.index },
    { label: props.country.name, current: true },
]);

const form = useForm({
    name: props.country.name,
    iso2: props.country.iso2 ?? '',
    iso3: props.country.iso3,
});

const submit = (): void => {
    form.put(props.urls.update);
};

// Countries with states are live reference data; remove the states first.
const deleteBlockedReason = computed<string>(() =>
    (props.states.length > 0 ? t('countries.delete_blocked_states') : ''));

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};

const stateColumns = [
    { key: 'code', label: t('countries.column_state_code'), width: '120px' },
    { key: 'name', label: t('countries.column_state_name'), width: 'minmax(0, 1fr)' },
    { key: 'remove', label: '', width: '60px', align: 'right' as const },
];

const newState = reactive({ name: '', code: '' });
const stateErrors = ref<Record<string, string>>({});

const addState = (): void => {
    router.post(props.urls.storeState, { ...newState }, {
        preserveScroll: true,
        onSuccess: () => {
            newState.name = '';
            newState.code = '';
            stateErrors.value = {};
        },
        onError: (errors) => {
            stateErrors.value = errors;
        },
    });
};

const removeState = (state: CountryState): void => {
    router.delete(state.urls.destroy, { preserveScroll: true });
};
</script>

<template>
    <SettingsShell :title="t('countries.edit_title', { name: country.name })" :breadcrumbs="breadcrumbs">
        <template #actions>
            <Tooltip :text="deleteBlockedReason">
                <Button variant="ghost" icon="trash" :disabled="!!deleteBlockedReason" @click="deleting = true">{{ t('common.delete') }}</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <Section :title="t('countries.section_details')">
            <div class="grid sm:grid-cols-3 gap-3">
                <div>
                    <FieldLabel required>{{ t('countries.field_iso2') }}</FieldLabel>
                    <TextInput v-model="form.iso2" mono :invalid="!!form.errors.iso2" />
                    <div v-if="form.errors.iso2" class="mt-1 text-[11px] text-danger">{{ form.errors.iso2 }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('countries.field_iso3') }}</FieldLabel>
                    <TextInput v-model="form.iso3" mono :invalid="!!form.errors.iso3" />
                    <div v-if="form.errors.iso3" class="mt-1 text-[11px] text-danger">{{ form.errors.iso3 }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('countries.field_name') }}</FieldLabel>
                    <TextInput v-model="form.name" :invalid="!!form.errors.name" />
                    <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                </div>
            </div>
        </Section>

        <Section :title="t('countries.section_states', { count: states.length })">
            <DataTable :columns="stateColumns" :rows="states">
                <template #cell-code="{ row }">
                    <span class="font-mono text-xs text-ink-700">{{ (row as unknown as CountryState).code || '—' }}</span>
                </template>
                <template #cell-name="{ row }">
                    <span class="text-[12.5px] text-ink-900">{{ (row as unknown as CountryState).name }}</span>
                </template>
                <template #cell-remove="{ row }">
                    <Tooltip :text="(row as unknown as CountryState).inTaxZone ? t('countries.state_delete_blocked') : ''">
                        <Button
                            variant="ghost"
                            size="sm"
                            icon="trash"
                            :aria-label="t('countries.remove_state')"
                            class="!w-[26px] !h-[26px] text-ink-700 hover:text-danger"
                            :disabled="(row as unknown as CountryState).inTaxZone"
                            @click.stop="removeState(row as unknown as CountryState)"
                        />
                    </Tooltip>
                </template>
                <template #empty>
                    <PageEmpty :title="t('countries.empty_states_title')" />
                </template>
            </DataTable>

            <div class="mt-3 grid sm:grid-cols-[120px_1fr_auto] gap-2">
                <div>
                    <TextInput v-model="newState.code" mono :invalid="!!stateErrors.code" :placeholder="t('countries.state_code_placeholder')" />
                    <div v-if="stateErrors.code" class="mt-1 text-[11px] text-danger">{{ stateErrors.code }}</div>
                </div>
                <div>
                    <TextInput v-model="newState.name" :invalid="!!stateErrors.name" :placeholder="t('countries.state_name_placeholder')" />
                    <div v-if="stateErrors.name" class="mt-1 text-[11px] text-danger">{{ stateErrors.name }}</div>
                </div>
                <Button icon="plus" @click="addState">{{ t('countries.add_state') }}</Button>
            </div>
        </Section>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('countries.confirm_delete_title')"
        :description="t('countries.confirm_delete_body', { name: country.name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
