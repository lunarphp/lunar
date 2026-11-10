#!/usr/bin/env node
/**
 * Imports one version's docs from a lunarphp/docs checkout into docs/ (spec 0094).
 *
 * Re-runnable: docs/ is replaced on every run (docs/README.md is kept), so docs
 * merged into lunarphp/docs meanwhile can be imported again. The version prefix
 * is dropped from page paths and root-relative links; the multirepo Action adds
 * it back when it publishes. Links into other versions keep their prefix.
 *
 * Run from the monorepo root: `node scripts/import-docs.mjs <docs-checkout> <version>`,
 * e.g. `node scripts/import-docs.mjs ../docs 2.x`.
 */
import { cpSync, existsSync, mkdirSync, readdirSync, readFileSync, rmSync, statSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const [source, version] = process.argv.slice(2);
if (!source || !version || !existsSync(join(source, version, '.'))) {
    console.error('Usage: node scripts/import-docs.mjs <docs-checkout> <version>');
    process.exit(1);
}

const target = 'docs';
const kept = new Set(['README.md']);
const ignored = new Set(['.DS_Store']);
const prefix = new RegExp(`^/${version.replace('.', '\\.')}(?=$|[/#?])`);

const FENCE = /^\s{0,3}(`{3,}|~{3,})/;

// Mirrors the link positions the multirepo Action rewrites, so the two round-trip.
function stripLinks(content) {
    const strip = (path) => path.replace(prefix, '') || '/';
    const rewrite = (text) =>
        text
            .replace(/(\]\(\s*<?)(\/(?!\/)[^)\s>]*)/g, (_, pre, path) => pre + strip(path))
            .replace(/^(\s{0,3}\[[^\]]+\]:\s*<?)(\/(?!\/)\S*)/gm, (_, pre, path) => pre + strip(path))
            .replace(/(\b(?:href|src|img|logo)=\{?\s*)(["'`])(\/(?!\/)[^"'`]*)\2/g, (_, pre, q, path) => pre + q + strip(path) + q);

    const out = [];
    let buffer = [];
    let fence;
    const flush = () => {
        if (buffer.length) out.push(rewrite(buffer.join('\n')));
        buffer = [];
    };
    for (const line of content.split('\n')) {
        const marker = FENCE.exec(line)?.[1];
        if (fence) {
            out.push(line);
            if (marker && marker[0] === fence[0] && marker.length >= fence.length) fence = undefined;
        } else if (marker) {
            flush();
            out.push(line);
            fence = marker;
        } else {
            buffer.push(line);
        }
    }
    flush();
    return out.join('\n');
}

function copyTree(from, to) {
    mkdirSync(to, { recursive: true });
    for (const name of readdirSync(from)) {
        if (ignored.has(name)) continue;
        const src = join(from, name);
        const dest = join(to, name);
        if (statSync(src).isDirectory()) {
            copyTree(src, dest);
        } else if (/\.mdx?$/.test(name)) {
            writeFileSync(dest, stripLinks(readFileSync(src, 'utf8')));
        } else {
            cpSync(src, dest);
        }
    }
}

function stripPages(node) {
    if (typeof node === 'string') return node.replace(new RegExp(`^${version.replace('.', '\\.')}/`), '');
    if (Array.isArray(node)) return node.map(stripPages);
    if (node && typeof node === 'object') {
        return Object.fromEntries(Object.entries(node).map(([k, v]) => [k, ['pages', 'groups', 'root'].includes(k) ? stripPages(v) : v]));
    }
    return node;
}

function pagesOf(node) {
    if (typeof node === 'string') return [node];
    if (Array.isArray(node)) return node.flatMap(pagesOf);
    if (node && typeof node === 'object') return ['pages', 'groups', 'root'].flatMap((k) => (k in node ? pagesOf(node[k]) : []));
    return [];
}

// The version's own tabs only; shared tabs (support/, logs/) stay in lunarphp/docs.
function buildConfig() {
    const site = JSON.parse(readFileSync(join(source, 'docs.json'), 'utf8'));
    const entry = site.navigation.versions.find((v) => v.version.startsWith(`v${version}`));
    if (!entry) throw new Error(`No v${version} entry in ${source}/docs.json`);
    const tabs = entry.tabs.filter((tab) => {
        const pages = pagesOf(tab);
        return pages.length > 0 && pages.every((p) => p.startsWith(`${version}/`));
    });

    // Site-wide settings for `mint dev` only; the published site takes them from lunarphp/docs.
    const { navigation, redirects, integrations, favicon, logo, ...settings } = site;
    return { ...settings, navigation: { tabs: stripPages(tabs) } };
}

const config = buildConfig();

if (existsSync(target)) {
    for (const name of readdirSync(target)) {
        if (!kept.has(name)) rmSync(join(target, name), { recursive: true, force: true });
    }
}
copyTree(join(source, version), target);
writeFileSync(join(target, 'docs.json'), JSON.stringify(config, null, 2) + '\n');

console.log(`Imported ${source}/${version} into ${target}/ (${config.navigation.tabs.map((t) => t.tab).join(', ')}).`);
