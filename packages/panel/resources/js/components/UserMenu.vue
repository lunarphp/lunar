<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
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
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from 'reka-ui';
import Icon from './Icon.vue';
import Tooltip from './Tooltip.vue';
import { useTheme, type ThemePreference } from '../composables/useTheme';

withDefaults(defineProps<{ collapsed?: boolean }>(), { collapsed: false });

type AuthUser = { name: string; email: string | null; avatar?: string | null } | null;

const { t } = useI18n();
const { theme, setTheme } = useTheme();

const user = computed<AuthUser>(() => (usePage().props.auth as { user: AuthUser } | undefined)?.user ?? null);
const displayName = computed(() => user.value?.name || 'Account');
const displayEmail = computed(() => user.value?.email ?? '');
const initial = computed(() => displayName.value.charAt(0).toUpperCase() || 'U');

// Gravatar with initial fallback when absent or unreachable (d=404).
const avatarFailed = ref(false);
const avatarUrl = computed(() => (user.value?.avatar && !avatarFailed.value ? user.value.avatar : ''));

const themeModel = computed<ThemePreference>({
    get: () => theme.value,
    set: (value) => setTheme(value),
});

const THEMES: { value: ThemePreference; labelKey: string; icon: string }[] = [
    { value: 'light', labelKey: 'nav.theme_light', icon: 'sun' },
    { value: 'dark', labelKey: 'nav.theme_dark', icon: 'moon' },
    { value: 'system', labelKey: 'nav.theme_system', icon: 'monitor' },
];

const locale = computed(() => (usePage().props.locale as string | undefined) ?? 'en');
const availableLocales = computed(() => (usePage().props.availableLocales as string[] | undefined) ?? []);
const isChangingLocale = ref(false);

/** Each locale rendered as its own endonym ("Deutsch", not "German"). */
function localeDisplayName(code: string): string {
    const bcp47 = code.replace('_', '-');

    try {
        const name = new Intl.DisplayNames([bcp47], { type: 'language' }).of(bcp47);

        return name ? name.charAt(0).toLocaleUpperCase(bcp47) + name.slice(1) : code;
    } catch {
        return code;
    }
}

const localeModel = computed<string>({
    get: () => locale.value,
    set: (value) => changeLocale(value),
});

// Persists the preference, then hard-reloads: app.ts boots vue-i18n from the
// shared locale prop once per page load, so a full re-boot is what fetches
// and applies the new locale's messages.
function changeLocale(next: string): void {
    if (isChangingLocale.value || next === locale.value) {
        return;
    }

    isChangingLocale.value = true;
    router.put(
        `/${panelPath.value}/account/locale`,
        { locale: next },
        {
            onSuccess: () => window.location.reload(),
            onError: () => {
                isChangingLocale.value = false;
            },
        },
    );
}

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
                :aria-label="t('common.open_user_menu')"
            >
                <Tooltip :text="collapsed ? displayName : ''">
                    <img
                        v-if="avatarUrl"
                        :src="avatarUrl"
                        :alt="displayName"
                        class="w-7 h-7 rounded-full object-cover shrink-0 ring-1 ring-line bg-surface-2"
                        @error="avatarFailed = true"
                    />
                    <span
                        v-else
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
                    {{ t('nav.theme') }}
                </DropdownMenuLabel>
                <DropdownMenuRadioGroup v-model="themeModel">
                    <DropdownMenuRadioItem v-for="option in THEMES" :key="option.value" :value="option.value" :class="radioBase">
                        <DropdownMenuItemIndicator class="absolute left-2 inline-flex items-center justify-center text-sage-ink">
                            <Icon name="check" cls="sm" />
                        </DropdownMenuItemIndicator>
                        <Icon :name="option.icon" cls="sm" class="text-ink-500" />
                        {{ t(option.labelKey) }}
                    </DropdownMenuRadioItem>
                </DropdownMenuRadioGroup>

                <DropdownMenuSeparator class="h-px bg-line my-1 -mx-1" />

                <DropdownMenuSub v-if="availableLocales.length > 1">
                    <DropdownMenuSubTrigger :class="itemBase">
                        <Icon name="globe" cls="sm" class="text-ink-500" />
                        {{ t('nav.language') }}
                        <span class="ml-auto flex items-center gap-1 text-[11px] text-ink-400">
                            {{ localeDisplayName(locale) }}
                            <Icon name="chevronRight" cls="sm" />
                        </span>
                    </DropdownMenuSubTrigger>
                    <DropdownMenuPortal>
                        <DropdownMenuSubContent
                            :side-offset="6"
                            :collision-padding="8"
                            class="z-50 min-w-[180px] max-h-72 overflow-y-auto bg-paper border border-line rounded-md shadow-lg p-1 outline-none"
                        >
                            <DropdownMenuRadioGroup v-model="localeModel">
                                <DropdownMenuRadioItem
                                    v-for="code in availableLocales"
                                    :key="code"
                                    :value="code"
                                    :disabled="isChangingLocale"
                                    :class="radioBase"
                                >
                                    <DropdownMenuItemIndicator class="absolute left-2 inline-flex items-center justify-center text-sage-ink">
                                        <Icon name="check" cls="sm" />
                                    </DropdownMenuItemIndicator>
                                    {{ localeDisplayName(code) }}
                                </DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuSubContent>
                    </DropdownMenuPortal>
                </DropdownMenuSub>

                <DropdownMenuSeparator v-if="availableLocales.length > 1" class="h-px bg-line my-1 -mx-1" />

                <DropdownMenuItem
                    :class="[itemBase, 'text-danger data-[highlighted]:bg-danger-soft data-[highlighted]:text-danger']"
                    @select="signOut"
                >
                    <Icon name="logOut" cls="sm" />
                    {{ t('nav.sign_out') }}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenuPortal>
    </DropdownMenuRoot>
</template>
