// @vitest-environment node
import { readFileSync } from 'node:fs';
import { describe, expect, it } from 'vitest';
import { addonThemePath, renderAddonTheme, themePath, tokenNames } from './generate-addon-theme.mjs';

describe('addon-theme.css', () => {
    const theme = readFileSync(themePath, 'utf8');

    it('matches what theme.css generates (run `npm run build:addon-theme` if this fails)', () => {
        expect(readFileSync(addonThemePath, 'utf8')).toBe(renderAddonTheme(theme));
    });

    it('aliases every panel token to the panel variable of the same name', () => {
        const names = tokenNames(theme);

        expect(names).toEqual(expect.arrayContaining(['--color-ink-400', '--color-chart-1-soft', '--shadow-md', '--animate-palette-out']));
        expect(new Set(names).size).toBe(names.length);
        expect(renderAddonTheme(theme)).toContain('  --color-ink-400: var(--color-ink-400);');
    });
});
