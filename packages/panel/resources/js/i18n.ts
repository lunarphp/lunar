import { createI18n, type I18n } from 'vue-i18n';
import type { LunarPanelRuntime } from './runtime/registry';

type PanelMessages = Record<string, Record<string, string>>;

type TranslationsResponse = {
    version: string;
    messages: PanelMessages;
};

const CACHE_PREFIX = 'lunar-panel-translations';

function versionPointerKey(locale: string): string {
    return `${CACHE_PREFIX}-${locale}-version`;
}

function messagesCacheKey(locale: string, version: string): string {
    return `${CACHE_PREFIX}-${locale}-${version}`;
}

function readCachedMessages(locale: string, version: string): PanelMessages | null {
    if (typeof window === 'undefined') {
        return null;
    }

    const raw = window.localStorage.getItem(messagesCacheKey(locale, version));

    if (!raw) {
        return null;
    }

    try {
        return JSON.parse(raw) as PanelMessages;
    } catch {
        return null;
    }
}

function writeCachedMessages(locale: string, version: string, messages: PanelMessages): void {
    if (typeof window === 'undefined') {
        return;
    }

    const currentKey = messagesCacheKey(locale, version);
    const prefix = `${CACHE_PREFIX}-${locale}-`;

    // Prune any previous version's blob for this locale so localStorage doesn't
    // accumulate one entry per historical translation change.
    for (let index = window.localStorage.length - 1; index >= 0; index -= 1) {
        const key = window.localStorage.key(index);

        if (key && key.startsWith(prefix) && key !== currentKey && !key.endsWith('-version')) {
            window.localStorage.removeItem(key);
        }
    }

    window.localStorage.setItem(currentKey, JSON.stringify(messages));
    window.localStorage.setItem(versionPointerKey(locale), version);
}

async function fetchTranslations(panelPath: string, locale: string): Promise<TranslationsResponse> {
    const response = await fetch(`/${panelPath}/translations/${locale}`, {
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        throw new Error(`Failed to load translations for locale "${locale}" (${response.status}).`);
    }

    return (await response.json()) as TranslationsResponse;
}

export function createPanelI18n(locale: string): I18n<Record<string, PanelMessages>, {}, {}, string, false> {
    return createI18n({
        legacy: false,
        locale,
        fallbackLocale: 'en',
        messages: {},
    });
}

/**
 * Loads translations into `i18n`'s message store for `locale`, then wires
 * `LunarPanelRuntime.registerTranslations()` so add-ons pushing messages at
 * runtime land in the same store.
 *
 * Cache-first, stale-while-revalidate: a fresh localStorage hit (keyed by the
 * server's mtime-derived version hash) is applied synchronously so guest
 * screens (login, two-factor, reset) never flash raw translation keys on
 * repeat visits; the endpoint is still queried in the background afterwards
 * to pick up a changed version. On a cold cache (first-ever visit) the fetch
 * is awaited before returning, so the caller can defer mounting the app
 * rather than rendering with empty messages.
 *
 * We intentionally don't bundle a static English fallback into the JS build
 * to paper over that first-visit case — it would duplicate resources/lang/en
 * in two places that could drift. A one-time network wait on the very first
 * visit is the accepted tradeoff.
 */
export async function bootTranslations(
    i18n: I18n<Record<string, PanelMessages>, {}, {}, string, false>,
    runtime: LunarPanelRuntime,
    panelPath: string,
    locale: string,
): Promise<void> {
    const cachedVersion = typeof window !== 'undefined' ? window.localStorage.getItem(versionPointerKey(locale)) : null;
    const cachedMessages = cachedVersion ? readCachedMessages(locale, cachedVersion) : null;

    if (cachedMessages) {
        i18n.global.setLocaleMessage(locale, cachedMessages);
    }

    const refresh = async (): Promise<void> => {
        const payload = await fetchTranslations(panelPath, locale);

        if (payload.version === cachedVersion) {
            return;
        }

        i18n.global.setLocaleMessage(locale, payload.messages);
        writeCachedMessages(locale, payload.version, payload.messages);
    };

    if (cachedMessages) {
        // Don't block first paint on a cache hit; refresh quietly in case the
        // version changed since it was cached.
        refresh().catch((error) => console.warn('[LunarPanel] Failed to refresh translations.', error));
    } else {
        // No cache to fall back on, so a failed fetch here just leaves
        // vue-i18n's messages empty; `t()` calls fall back to the raw key,
        // matching the old shim's behaviour for a missing string.
        await refresh().catch((error) => console.warn('[LunarPanel] Failed to load translations.', error));
    }

    runtime.onTranslationsRegistered((addonLocale, namespace, messages) => {
        i18n.global.mergeLocaleMessage(addonLocale, { [namespace]: messages });
    });
}
