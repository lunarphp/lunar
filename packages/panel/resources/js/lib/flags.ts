// Emoji flag for a language code, built from regional-indicator code points
// (keeps the source ASCII-only). Locale codes with a region subtag (en-GB,
// pt_BR) use the region; bare language codes fall back to a representative
// region, defaulting to the language code itself, which matches for most
// European languages (de, fr, nl, ...). Unknown codes return '' and callers
// render nothing.
const LANGUAGE_REGIONS: Record<string, string> = {
    ar: 'sa',
    cs: 'cz',
    da: 'dk',
    el: 'gr',
    en: 'gb',
    fa: 'ir',
    he: 'il',
    hi: 'in',
    ja: 'jp',
    ko: 'kr',
    sv: 'se',
    uk: 'ua',
    vi: 'vn',
    zh: 'cn',
};

export function flagForLanguage(code: string): string {
    const [language, region] = code.toLowerCase().replace('_', '-').split('-');

    const target = region ?? LANGUAGE_REGIONS[language] ?? language;

    if (!/^[a-z]{2}$/.test(target)) {
        return '';
    }

    const REGIONAL_INDICATOR_OFFSET = 0x1f1e6 - 0x61; // 'a' maps onto the regional indicator block

    return String.fromCodePoint(
        ...[...target].map((char) => char.charCodeAt(0) + REGIONAL_INDICATOR_OFFSET),
    );
}
