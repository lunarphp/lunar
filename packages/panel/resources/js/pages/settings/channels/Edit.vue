<script setup lang="ts">
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import Button from '../../../components/Button.vue';
import ConfirmDialog from '../../../components/ConfirmDialog.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import Section from '../../../components/Section.vue';
import Select from '../../../components/Select.vue';
import TextInput from '../../../components/TextInput.vue';
import Toggle from '../../../components/Toggle.vue';
import Tooltip from '../../../components/Tooltip.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type Channel = {
    id: number;
    name: string;
    handle: string;
    url: string | null;
    default: boolean;
    status: string | null;
};

const props = defineProps<{
    channel: Channel;
    hasOrderHistory: boolean;
    urls: { update: string; destroy: string; index: string };
}>();

const form = useForm({
    name: props.channel.name,
    url: props.channel.url ?? '',
    default: props.channel.default,
    status: props.channel.status ?? 'active',
});

const submit = (): void => {
    form.put(props.urls.update);
};

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="`Edit channel — ${channel.name}`">
        <div class="flex justify-end gap-2 mb-4">
            <Tooltip :text="hasOrderHistory ? 'Cannot delete a channel with order history.' : ''">
                <Button variant="ghost" icon="trash" :disabled="hasOrderHistory" @click="deleting = true">Delete</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">Save</Button>
        </div>

        <Section title="Details">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required>Name</FieldLabel>
                    <TextInput v-model="form.name" :invalid="!!form.errors.name" />
                    <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                </div>
                <div>
                    <FieldLabel hint="auto-slugged from name">Handle</FieldLabel>
                    <TextInput :model-value="channel.handle" disabled mono />
                </div>
                <div class="sm:col-span-2">
                    <FieldLabel>URL</FieldLabel>
                    <TextInput v-model="form.url" type="url" :invalid="!!form.errors.url" placeholder="https://example.com" />
                    <div v-if="form.errors.url" class="mt-1 text-[11px] text-danger">{{ form.errors.url }}</div>
                </div>
            </div>
        </Section>

        <Section title="State">
            <div class="flex flex-col gap-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <Toggle :on="form.default" @toggle="form.default = !form.default" />
                    <div>
                        <div class="text-[12.5px] text-ink-900 font-medium">Default channel</div>
                        <div class="text-[11px] text-ink-500">Used when a request doesn't pick one explicitly.</div>
                    </div>
                </label>
                <div class="max-w-[220px]">
                    <FieldLabel>Status</FieldLabel>
                    <Select v-model="form.status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </Select>
                </div>
            </div>
        </Section>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        title="Delete channel?"
        :description="`&quot;${channel.name}&quot; will be permanently removed.`"
        confirm-label="Delete"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
