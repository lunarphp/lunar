import { usePage } from '@inertiajs/vue3';

/**
 * Interim translation lookup backed by the shared `lang.auth` Inertia prop.
 * Replaced by vue-i18n in spec 0049 slice 4.
 */
export function useAuthLang(): (key: string) => string {
    const props = usePage().props as { lang?: { auth?: Record<string, string> } };

    return (key) => props.lang?.auth?.[key] ?? key;
}
