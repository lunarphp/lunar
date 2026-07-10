<script setup lang="ts">
import { router, useForm, Link } from '@inertiajs/vue3';
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
    urls: { edit: string; destroy: string };
};

const props = defineProps<{
    channels: Channel[];
    urls: { store: string };
}>();

const createForm = useForm({
    name: '',
    url: '',
    default: false as boolean,
    status: 'active',
});

const submitCreate = () => {
    createForm.post(props.urls.store, {
        onSuccess: () => createForm.reset(),
    });
};

const destroyChannel = (channel: Channel) => {
    if (confirm(`Delete channel "${channel.name}"? This cannot be undone.`)) {
        router.delete(channel.urls.destroy);
    }
};
</script>

<template>
    <SettingsShell title="Channels">
        <section class="rounded-lg border border-line bg-paper p-5 mb-6">
            <h2 class="text-[14px] font-semibold text-ink-900 mb-3">Add a channel</h2>
            <form class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end" @submit.prevent="submitCreate">
                <div class="sm:col-span-2">
                    <FieldLabel required>Name</FieldLabel>
                    <TextInput v-model="createForm.name" :invalid="!!createForm.errors.name" placeholder="e.g. Webstore" />
                    <div v-if="createForm.errors.name" class="mt-1 text-[11px] text-danger">{{ createForm.errors.name }}</div>
                </div>
                <div class="sm:col-span-2">
                    <FieldLabel>URL</FieldLabel>
                    <TextInput v-model="createForm.url" :invalid="!!createForm.errors.url" placeholder="https://example.com" />
                    <div v-if="createForm.errors.url" class="mt-1 text-[11px] text-danger">{{ createForm.errors.url }}</div>
                </div>
                <div class="flex items-center gap-2">
                    <Checkbox v-model="createForm.default" aria-label="Default channel" />
                    <FieldLabel class="mb-0">Default</FieldLabel>
                </div>
                <div>
                    <FieldLabel>Status</FieldLabel>
                    <select
                        v-model="createForm.status"
                        class="w-full h-8 px-2.5 border rounded-md bg-surface text-[13px] text-ink-900 border-line-strong focus:outline-none focus:ring-3 focus:border-sage focus:ring-sage/35"
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div>
                    <Button type="submit" variant="primary" :disabled="createForm.processing">Create channel</Button>
                </div>
            </form>
        </section>

        <section class="rounded-lg border border-line bg-paper overflow-hidden">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-surface-2 text-[11px] uppercase tracking-wide text-ink-400">
                    <tr>
                        <th class="px-4 py-2 font-medium">Name</th>
                        <th class="px-4 py-2 font-medium">Handle</th>
                        <th class="px-4 py-2 font-medium">URL</th>
                        <th class="px-4 py-2 font-medium">Default</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                        <th class="px-4 py-2 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="channel in channels" :key="channel.id" class="border-t border-line">
                        <td class="px-4 py-2.5 text-ink-900 font-medium">{{ channel.name }}</td>
                        <td class="px-4 py-2.5 text-ink-500 font-mono text-xs">{{ channel.handle }}</td>
                        <td class="px-4 py-2.5 text-ink-500">{{ channel.url ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <span
                                v-if="channel.default"
                                class="inline-flex items-center rounded-full bg-sage-soft px-2 py-0.5 text-[11px] font-medium text-sage-ink"
                            >Default</span>
                        </td>
                        <td class="px-4 py-2.5 capitalize text-ink-700">{{ channel.status ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <Link
                                :href="channel.urls.edit"
                                class="text-ink-600 hover:text-ink-900 text-[12px] font-medium mr-3"
                            >Edit</Link>
                            <button
                                type="button"
                                class="text-danger hover:underline text-[12px] font-medium"
                                @click="destroyChannel(channel)"
                            >Delete</button>
                        </td>
                    </tr>
                    <tr v-if="channels.length === 0">
                        <td colspan="6" class="px-4 py-6 text-center text-ink-400">No channels yet.</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </SettingsShell>
</template>
