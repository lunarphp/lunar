<script setup lang="ts">
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import {
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuItemIndicator,
    DropdownMenuLabel,
    DropdownMenuPortal,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuRoot,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from 'reka-ui';
import Icon from './Icon.vue';
import Tooltip from './Tooltip.vue';
import { useTheme, type ThemePreference } from '../composables/useTheme';

withDefaults(defineProps<{ collapsed?: boolean }>(), { collapsed: false });

type AuthUser = { name: string; email: string | null } | null;

const { theme, setTheme } = useTheme();

const user = computed<AuthUser>(() => (usePage().props.auth as { user: AuthUser } | undefined)?.user ?? null);
const displayName = computed(() => user.value?.name || 'Account');
const displayEmail = computed(() => user.value?.email ?? '');
const initial = computed(() => displayName.value.charAt(0).toUpperCase() || 'U');

const themeModel = computed<ThemePreference>({
    get: () => theme.value,
    set: (value) => setTheme(value),
});

const THEMES: { value: ThemePreference; label: string; icon: string }[] = [
    { value: 'light', label: 'Light', icon: 'sun' },
    { value: 'dark', label: 'Dark', icon: 'moon' },
    { value: 'system', label: 'System', icon: 'monitor' },
];

const panelPath = computed(() => (usePage().props.panel as { path: string }).path);
const logoutUrl = computed(() => `/${panelPath.value}/logout`);
const signOut = () => router.post(logoutUrl.value);

const itemBase =
    'relative flex items-center gap-2.5 px-2 py-1.5 rounded-sm text-[13px] text-ink-700 cursor-pointer select-none outline-none data-[highlighted]:bg-surface-2 data-[highlighted]:text-ink-900';

const radioBase =
    'relative flex items-center gap-2.5 pl-7 pr-2 py-1.5 rounded-sm text-[13px] text-ink-700 cursor-pointer select-none outline-none data-[highlighted]:bg-surface-2 data-[highlighted]:text-ink-900 data-[state=checked]:text-ink-900 data-[state=checked]:font-medium';
</script>

<template>
    <DropdownMenuRoot>
        <DropdownMenuTrigger as-child>
            <button
                type="button"
                :class="[
                    'flex items-center gap-2.5 border-t border-line mt-1.5 w-full text-left transition-colors',
                    'focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-sage/35 focus-visible:rounded-md',
                    'hover:bg-surface-2 data-[state=open]:bg-surface-2',
                    collapsed ? 'justify-center py-2 px-0' : 'p-2',
                ]"
                aria-label="Open user menu"
            >
                <Tooltip :text="collapsed ? displayName : ''">
                    <span
                        class="w-7 h-7 rounded-full bg-ink-900 text-paper text-xs font-semibold grid place-items-center shrink-0 ring-1 ring-line"
                    >{{ initial }}</span>
                </Tooltip>
                <template v-if="!collapsed">
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-medium text-ink-900 truncate">{{ displayName }}</div>
                        <div v-if="displayEmail" class="text-[11px] text-ink-500 truncate">{{ displayEmail }}</div>
                    </div>
                    <Icon name="chevUpDown" cls="sm" class="text-ink-400" />
                </template>
            </button>
        </DropdownMenuTrigger>

        <DropdownMenuPortal>
            <DropdownMenuContent
                :side-offset="6"
                :collision-padding="8"
                side="top"
                align="start"
                class="z-50 min-w-[240px] bg-paper border border-line rounded-md shadow-lg p-1 outline-none"
            >
                <div class="px-2 py-2">
                    <div class="text-xs font-medium text-ink-900 truncate">{{ displayName }}</div>
                    <div v-if="displayEmail" class="text-[11px] text-ink-500 truncate">{{ displayEmail }}</div>
                </div>

                <DropdownMenuSeparator class="h-px bg-line my-1 -mx-1" />

                <DropdownMenuLabel class="px-2 pt-1.5 pb-1 text-[10px] uppercase tracking-[0.08em] text-ink-400 font-medium">
                    Theme
                </DropdownMenuLabel>
                <DropdownMenuRadioGroup v-model="themeModel">
                    <DropdownMenuRadioItem v-for="t in THEMES" :key="t.value" :value="t.value" :class="radioBase">
                        <DropdownMenuItemIndicator class="absolute left-2 inline-flex items-center justify-center text-sage-ink">
                            <Icon name="check" cls="sm" />
                        </DropdownMenuItemIndicator>
                        <Icon :name="t.icon" cls="sm" class="text-ink-500" />
                        {{ t.label }}
                    </DropdownMenuRadioItem>
                </DropdownMenuRadioGroup>

                <DropdownMenuSeparator class="h-px bg-line my-1 -mx-1" />

                <DropdownMenuItem
                    :class="[itemBase, 'text-danger data-[highlighted]:bg-danger-soft data-[highlighted]:text-danger']"
                    @select="signOut"
                >
                    <Icon name="logOut" cls="sm" />
                    Sign out
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenuPortal>
    </DropdownMenuRoot>
</template>
