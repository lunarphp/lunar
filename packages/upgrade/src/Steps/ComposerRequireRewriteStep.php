<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Steps;

use Illuminate\Support\Facades\File;
use Lunar\Upgrade\Support\StepReport;

/**
 * Rewrites the consuming application's `composer.json` for the v2 install model:
 *
 * - Swaps `lunarphp/lunar` → `lunarphp/admin` in `require` / `require-dev`.
 * - Adds an explicit `lunarphp/core` require if it was only present transitively.
 *
 * Implemented as a structured JSON edit so the file's other fields, formatting
 * indent, and ordering decisions are preserved. Spec 0006.
 */
class ComposerRequireRewriteStep implements UpgradeStep
{
    public function name(): string
    {
        return 'composer-require-rewrite';
    }

    public function label(): string
    {
        return 'Rewrite composer.json for the v2 install model';
    }

    public function specReference(): string
    {
        return '0006-filament-bridge-package';
    }

    public function run(StepContext $context): void
    {
        $composerPath = base_path('composer.json');

        if (! File::exists($composerPath)) {
            $context->report->record(
                $this->name(),
                StepReport::STATUS_SKIPPED,
                'No composer.json found at the project root.',
            );

            return;
        }

        $contents = File::get($composerPath);
        $decoded = json_decode($contents, true);

        if (! is_array($decoded)) {
            $context->report->record(
                $this->name(),
                StepReport::STATUS_SKIPPED,
                'composer.json could not be decoded.',
            );

            return;
        }

        $changes = [];
        $decoded = $this->rewriteRequire($decoded, 'require', $changes);
        $decoded = $this->rewriteRequire($decoded, 'require-dev', $changes);

        if ($changes === []) {
            $context->report->record(
                $this->name(),
                StepReport::STATUS_SKIPPED,
                'composer.json already aligned with the v2 install model.',
            );

            return;
        }

        if ($context->dryRun) {
            $context->report->record(
                $this->name(),
                StepReport::STATUS_DRY_RUN,
                'Would apply: '.implode('; ', $changes).'. Then run `composer update`.',
            );

            return;
        }

        $indent = $this->detectIndent($contents);

        File::put(
            $composerPath,
            json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n",
        );

        if ($indent !== '    ') {
            $this->reindent($composerPath, $indent);
        }

        $context->report->record(
            $this->name(),
            StepReport::STATUS_OK,
            'Applied: '.implode('; ', $changes).'. Run `composer update` to refresh the lockfile.',
        );
    }

    /**
     * @param  array<string, mixed>  $decoded
     * @param  array<int, string>  $changes
     * @return array<string, mixed>
     */
    protected function rewriteRequire(array $decoded, string $section, array &$changes): array
    {
        if (! isset($decoded[$section]) || ! is_array($decoded[$section])) {
            return $decoded;
        }

        $require = $decoded[$section];

        if (array_key_exists('lunarphp/lunar', $require)) {
            $version = $require['lunarphp/lunar'];
            unset($require['lunarphp/lunar']);
            $require['lunarphp/admin'] = $version;
            $changes[] = "{$section}: lunarphp/lunar → lunarphp/admin";

            if (! array_key_exists('lunarphp/core', $require)) {
                $require['lunarphp/core'] = $version;
                $changes[] = "{$section}: added explicit lunarphp/core";
            }
        }

        ksort($require);
        $decoded[$section] = $require;

        return $decoded;
    }

    protected function detectIndent(string $contents): string
    {
        foreach (preg_split("/\r?\n/", $contents) as $line) {
            if (preg_match('/^(\s+)\S/', $line, $match)) {
                return $match[1];
            }
        }

        return '    ';
    }

    protected function reindent(string $path, string $indent): void
    {
        $contents = File::get($path);
        $rewritten = preg_replace_callback(
            '/^( {4,})/m',
            function (array $match) use ($indent): string {
                $depth = intdiv(strlen($match[1]), 4);

                return str_repeat($indent, $depth);
            },
            $contents,
        );

        File::put($path, $rewritten ?? $contents);
    }
}
