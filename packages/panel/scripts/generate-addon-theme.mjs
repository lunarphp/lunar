#!/usr/bin/env node
/**
 * Writes resources/panel-package/addon-theme.css from resources/css/theme.css.
 *
 * Every panel token becomes an alias of the panel's own variable, so an add-on
 * building with a Tailwind `prefix()` resolves its classes to the variables the
 * panel puts on the page (and so follows the `.dark` overrides) instead of to a
 * prefixed copy the panel never defines.
 *
 * Run from packages/panel: `npm run build:addon-theme`. scripts/addon-theme.test.mjs
 * fails when the committed file is stale.
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';

export const themePath = fileURLToPath(new URL('../resources/css/theme.css', import.meta.url));
export const addonThemePath = fileURLToPath(new URL('../resources/panel-package/addon-theme.css', import.meta.url));

const HEADER = `/*
 * GENERATED from theme.css by scripts/generate-addon-theme.mjs. Do not edit;
 * run \`npm run build:addon-theme\` in packages/panel instead.
 *
 * The panel's design tokens for an add-on's own Tailwind build. Each token
 * aliases the variable the panel already defines on the page, so add-on
 * classes follow the panel's light and dark values:
 *
 *   @import 'tailwindcss/theme.css' theme(reference) prefix(acme);
 *   @import '@lunarphp/panel/addon-theme.css' theme(reference inline);
 *   @import 'tailwindcss/utilities.css' layer(utilities) source(none);
 *   @custom-variant dark (&:where(.dark, .dark *));
 *   @source './js';
 *
 * Use a prefix unique to the add-on and put it on every class, e.g.
 * \`acme:hidden acme:md:flex\`. See the panel-addon-example README.
 */
`;

export function tokenNames(themeSource) {
    const block = themeSource.match(/@theme\b[^{]*\{([\s\S]*?)\n\}/);
    if (!block) {
        throw new Error('No @theme block found in theme.css');
    }

    const body = block[1].replace(/\/\*[\s\S]*?\*\//g, '');

    return [...body.matchAll(/^\s*(--[a-z0-9-]+)\s*:/gm)].map((match) => match[1]);
}

export function renderAddonTheme(themeSource) {
    const lines = tokenNames(themeSource).map((name) => `  ${name}: var(${name});`);

    return `${HEADER}\n@theme {\n${lines.join('\n')}\n}\n`;
}

if (process.argv[1] === fileURLToPath(import.meta.url)) {
    writeFileSync(addonThemePath, renderAddonTheme(readFileSync(themePath, 'utf8')));
}
