#!/usr/bin/env node
/**
 * Fails when a publishable npm package's content differs from the tarball
 * already published under the version its package.json declares.
 *
 * Stateless by design: the registry is the record of what has shipped, so the
 * check is simply "does npm pack of this tree byte-match the published
 * tarball?". A version absent from the registry passes — it publishes on the
 * next tag. See publish_npm.yml for the other half of the invariant (publish
 * skips versions that already exist).
 *
 * Run from the monorepo root: `npm run check-npm-drift`. The @lunarphp/panel
 * types must be built first (`npm run build:types` in packages/panel).
 */
import { execFileSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { existsSync, mkdtempSync, readdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, relative } from 'node:path';

const PACKAGES = {
    '@lunarphp/panel': {
        dir: 'packages/panel/resources/panel-package',
        // npm pack runs prepack, not prepublishOnly, so the gitignored types
        // are only as fresh as the last build — refuse to compare without them.
        requires: 'packages/panel/resources/panel-package/dist/ui.d.ts',
        hint: 'run `npm run build:types` in packages/panel first',
    },
    '@lunarphp/panel-vite-plugin': {
        dir: 'packages/panel/resources/package',
    },
};

const npm = (...args) => execFileSync('npm', args, { encoding: 'utf8' }).trim();

// As npm(), but without npm's stderr leaking into the log — for probes where
// an error (E404) is an expected, healthy outcome.
const npmQuiet = (...args) => execFileSync('npm', args, { encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] }).trim();

function hashTree(root) {
    const hashes = new Map();
    const walk = (dir) => {
        for (const entry of readdirSync(dir, { withFileTypes: true })) {
            const path = join(dir, entry.name);
            if (entry.isDirectory()) {
                walk(path);
            } else {
                hashes.set(relative(root, path), createHash('sha256').update(readFileSync(path)).digest('hex'));
            }
        }
    };
    walk(root);
    return hashes;
}

async function checkPackage(name, { dir, requires, hint }) {
    const version = JSON.parse(readFileSync(join(dir, 'package.json'), 'utf8')).version;

    let tarballUrl;
    try {
        tarballUrl = npmQuiet('view', `${name}@${version}`, 'dist.tarball');
    } catch {
        console.log(`PASS ${name}@${version} — not on the registry yet, publishes on the next tag`);
        return true;
    }

    if (requires && !existsSync(requires)) {
        throw new Error(`${name}: ${requires} is missing — ${hint}`);
    }

    const work = mkdtempSync(join(tmpdir(), 'npm-drift-'));
    try {
        const response = await fetch(tarballUrl);
        if (!response.ok) {
            throw new Error(`${name}: failed to download ${tarballUrl} (${response.status})`);
        }
        writeFileSync(join(work, 'published.tgz'), Buffer.from(await response.arrayBuffer()));

        const packed = npm('pack', '--workspace', name, '--pack-destination', work, '--loglevel=error').split('\n').pop();

        for (const [tgz, out] of [['published.tgz', 'published'], [packed, 'local']]) {
            execFileSync('mkdir', ['-p', join(work, out)]);
            execFileSync('tar', ['-xzf', join(work, tgz), '-C', join(work, out)]);
        }

        // Both tarballs root their files under package/.
        const published = hashTree(join(work, 'published', 'package'));
        const local = hashTree(join(work, 'local', 'package'));

        const drift = [];
        for (const [file, hash] of local) {
            if (!published.has(file)) {
                drift.push(`only in local pack: ${file}`);
            } else if (published.get(file) !== hash) {
                drift.push(`differs: ${file}`);
            }
        }
        for (const file of published.keys()) {
            if (!local.has(file)) {
                drift.push(`only in published tarball: ${file}`);
            }
        }

        if (drift.length > 0) {
            console.error(`FAIL ${name}@${version} — content differs from the published tarball:`);
            for (const line of drift) {
                console.error(`  ${line}`);
            }
            console.error(`  Bump the version in ${dir}/package.json so the change publishes on the next tag.`);
            return false;
        }

        console.log(`PASS ${name}@${version} — matches the published tarball`);
        return true;
    } finally {
        rmSync(work, { recursive: true, force: true });
    }
}

let ok = true;
for (const [name, config] of Object.entries(PACKAGES)) {
    ok = (await checkPackage(name, config)) && ok;
}
process.exit(ok ? 0 : 1);
