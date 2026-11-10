<?php

declare(strict_types=1);

/**
 * Diff Lunar translation files: report keys present in en/ but missing in other locales,
 * and keys present in other locales but no longer in en/ (stale).
 *
 * Usage: php .claude/skills/lunar-pr-review/scripts/missing-translations.php [path-glob]
 *
 * Exits 0 always — informational. Output is structured for an agent to parse.
 */

$repoRoot = realpath(__DIR__.'/../../../..');

if ($repoRoot === false) {
    fwrite(STDERR, "Could not resolve repo root\n");
    exit(0);
}

// Some lang files reference application classes (e.g. discount.php uses Lunar\Models\Discount).
// Boot the Composer autoloader so those files load. Optional — script degrades gracefully if absent.
$autoload = $repoRoot.'/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}

$langDirs = glob($repoRoot.'/packages/*/resources/lang', GLOB_ONLYDIR) ?: [];

if ($langDirs === []) {
    echo "No lang directories found under packages/*/resources/lang\n";
    exit(0);
}

$totalMissing = 0;
$totalStale = 0;

foreach ($langDirs as $langDir) {
    $package = basename(dirname(dirname($langDir)));
    $enDir = $langDir.'/en';

    if (! is_dir($enDir)) {
        continue;
    }

    $locales = array_values(array_filter(
        glob($langDir.'/*', GLOB_ONLYDIR) ?: [],
        fn ($d) => basename($d) !== 'en',
    ));

    $enFiles = glob($enDir.'/*.php') ?: [];

    foreach ($enFiles as $enFile) {
        $fileName = basename($enFile);
        $enKeys = flatten_keys(safe_require($enFile));

        foreach ($locales as $localeDir) {
            $locale = basename($localeDir);
            $peer = $localeDir.'/'.$fileName;

            if (! is_file($peer)) {
                $rel = relative($enFile, $repoRoot);
                $count = count($enKeys);
                echo "[missing-file] packages/{$package} {$locale}/{$fileName} — no peer for {$rel} ({$count} keys)\n";
                $totalMissing += $count;
                continue;
            }

            $peerKeys = flatten_keys(safe_require($peer));

            $missing = array_diff($enKeys, $peerKeys);
            $stale = array_diff($peerKeys, $enKeys);

            if ($missing !== []) {
                $totalMissing += count($missing);
                $sample = array_slice($missing, 0, 10);
                $more = count($missing) > 10 ? ' (+'.(count($missing) - 10).' more)' : '';
                echo "[missing] packages/{$package}/resources/lang/{$locale}/{$fileName}: ".implode(', ', $sample).$more."\n";
            }

            if ($stale !== []) {
                $totalStale += count($stale);
                $sample = array_slice($stale, 0, 10);
                $more = count($stale) > 10 ? ' (+'.(count($stale) - 10).' more)' : '';
                echo "[stale] packages/{$package}/resources/lang/{$locale}/{$fileName}: ".implode(', ', $sample).$more."\n";
            }
        }
    }
}

echo "\nSummary: {$totalMissing} missing key(s), {$totalStale} stale key(s)\n";
exit(0);

function safe_require(string $file): array
{
    try {
        $value = require $file;
    } catch (\Throwable $e) {
        fwrite(STDERR, "Failed to load {$file}: {$e->getMessage()}\n");
        return [];
    }

    return is_array($value) ? $value : [];
}

function flatten_keys(array $array, string $prefix = ''): array
{
    $out = [];

    foreach ($array as $key => $value) {
        $compound = $prefix === '' ? (string) $key : $prefix.'.'.$key;

        if (is_array($value) && $value !== [] && ! array_is_list($value)) {
            $out = array_merge($out, flatten_keys($value, $compound));
            continue;
        }

        $out[] = $compound;
    }

    return $out;
}

function relative(string $path, string $root): string
{
    return str_starts_with($path, $root.'/') ? substr($path, strlen($root) + 1) : $path;
}
