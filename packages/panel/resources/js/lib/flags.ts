// ISO 3166-1 alpha-2 region code for a language code, for feeding <Flag>.
// Locale codes with a region subtag (en-GB, pt_BR) use the region; bare
// language codes fall back to a representative region, defaulting to the
// language code itself, which matches for most European languages (de, fr,
// nl, ...). Unknown codes return '' and callers render nothing.
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

export function regionForLanguage(code: string): string {
    const [language, region] = code.toLowerCase().replace('_', '-').split('-');

    const target = region ?? LANGUAGE_REGIONS[language] ?? language;

    return /^[a-z]{2}$/.test(target) ? target : '';
}
