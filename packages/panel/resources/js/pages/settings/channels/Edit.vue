<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import Button from '../../../components/Button.vue';
import Checkbox from '../../../components/Checkbox.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import TextInput from '../../../components/TextInput.vue';
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

const submit = () => {
    form.put(props.urls.update);
};

const destroyChannel = () => {
    if (props.hasOrderHistory) {
        return;
    }

    if (confirm(`Delete channel "${props.channel.name}"? This cannot be undone.`)) {
        router.delete(props.urls.destroy);
    }
};
</script>

<template>
    <SettingsShell :title="`Edit channel — ${channel.name}`">
        <section class="rounded-lg border border-line bg-paper p-6 max-w-lg">
            <form class="flex flex-col gap-4" @submit.prevent="submit">
                <div>
                    <FieldLabel required>Name</FieldLabel>
                    <TextInput v-model="form.name" :invalid="!!form.errors.name" />
                    <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                </div>

                <div>
                    <FieldLabel hint="auto-slugged from name">Handle</FieldLabel>
                    <TextInput :model-value="channel.handle" disabled mono />
                </div>

                <div>
                    <FieldLabel>URL</FieldLabel>
                    <TextInput v-model="form.url" :invalid="!!form.errors.url" placeholder="https://example.com" />
                    <div v-if="form.errors.url" class="mt-1 text-[11px] text-danger">{{ form.errors.url }}</div>
                </div>

                <div>
                    <FieldLabel>Status</FieldLabel>
                    <select
                        v-model="form.status"
                        class="w-full h-8 px-2.5 border rounded-md bg-surface text-[13px] text-ink-900 border-line-strong focus:outline-none focus:ring-3 focus:border-sage focus:ring-sage/35"
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox v-model="form.default" aria-label="Default channel" />
                    <FieldLabel class="mb-0">Default channel</FieldLabel>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <Button type="submit" variant="primary" :disabled="form.processing">Save changes</Button>

                    <button
                        type="button"
                        class="text-[12px] font-medium text-danger disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="hasOrderHistory"
                        :title="hasOrderHistory ? 'Cannot delete a channel with order history.' : ''"
                        @click="destroyChannel"
                    >Delete channel</button>
                </div>
            </form>
        </section>
    </SettingsShell>
</template>
