import { describe, expect, it } from 'vitest';
import { flagForLanguage } from './flags';

const flag = (region: string): string =>
    String.fromCodePoint(...[...region].map((char) => char.charCodeAt(0) - 0x61 + 0x1f1e6));

describe('flagForLanguage', () => {
    it('uses the region subtag when present', () => {
        expect(flagForLanguage('en-GB')).toBe(flag('gb'));
        expect(flagForLanguage('pt_BR')).toBe(flag('br'));
    });

    it('maps bare language codes to a representative region', () => {
        expect(flagForLanguage('en')).toBe(flag('gb'));
        expect(flagForLanguage('de')).toBe(flag('de'));
        expect(flagForLanguage('vi')).toBe(flag('vn'));
    });

    it('returns an empty string for unknown shapes', () => {
        expect(flagForLanguage('eng')).toBe('');
        expect(flagForLanguage('')).toBe('');
    });
});
