import { describe, expect, it } from 'vitest';
import { regionForLanguage } from './flags';

describe('regionForLanguage', () => {
    it('uses the region subtag when present', () => {
        expect(regionForLanguage('en-GB')).toBe('gb');
        expect(regionForLanguage('pt_BR')).toBe('br');
    });

    it('maps bare language codes to a representative region', () => {
        expect(regionForLanguage('en')).toBe('gb');
        expect(regionForLanguage('de')).toBe('de');
        expect(regionForLanguage('vi')).toBe('vn');
    });

    it('returns an empty string for unknown shapes', () => {
        expect(regionForLanguage('eng')).toBe('');
        expect(regionForLanguage('')).toBe('');
    });
});
