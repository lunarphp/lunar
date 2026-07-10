import { usePage } from '@inertiajs/vue3';

/**
 * Generic translation lookup backed by the shared `lang.<namespace>` Inertia prop.
 */
export function useLang(namespace: string): (key: string) => string {
    const props = usePage().props as { lang?: Record<string, Record<string, string>> };

    return (key) => props.lang?.[namespace]?.[key] ?? key;
}

/**
 * Interim translation lookup backed by the shared `lang.auth` Inertia prop.
 * Kept for backward compatibility with slice 3 auth pages.
 */
export function useAuthLang(): (key: string) => string {
    return useLang('auth');
}
