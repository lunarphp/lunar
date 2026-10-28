<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Steps;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Lunar\Core\Enums\StepList;
use Lunar\Core\Manifests\StepManifest;
use Lunar\Upgrade\Rector\LunarSetList;
use Lunar\Upgrade\Support\StepReport;

/**
 * Reports the step lists a published config file still defines. v2 registers
 * them in code (spec 0093), and reading a list fails while its key is defined.
 *
 * A list that matches the core defaults is safe to delete. A customised list is
 * reported with the equivalent `Steps::` calls to paste into a service provider.
 * The config files are not edited.
 */
class StepListConfigStep implements UpgradeStep
{
    /**
     * Published config files, loaded once each.
     *
     * @var array<string, array<string, mixed>|null>
     */
    protected array $publishedConfig = [];

    public function __construct(protected StepManifest $manifest) {}

    public function name(): string
    {
        return 'step-list-config';
    }

    public function label(): string
    {
        return 'Move config step lists to Steps registrations';
    }

    public function specReference(): string
    {
        return '0093-registrable-calculation-steps';
    }

    public function run(StepContext $context): void
    {
        $safeToDelete = [];
        $manualActions = [];
        $customised = 0;

        foreach (StepList::cases() as $list) {
            $configured = $this->publishedList($list);

            if ($configured === null) {
                continue;
            }

            $key = $list->legacyConfigKey();
            $calls = $this->registrationsFor($list, $configured);

            if ($calls === []) {
                $safeToDelete[] = $key;

                continue;
            }

            $customised++;
            $manualActions[] = "Remove [{$key}] from your config and register the difference in a service provider:\n"
                .'    Steps::for(StepList::'.$list->name.')'.implode('', array_map(fn (string $call): string => "\n        {$call}", $calls)).';';
        }

        if ($safeToDelete === [] && $manualActions === []) {
            $context->report->record(
                $this->name(),
                StepReport::STATUS_SKIPPED,
                'No published config defines a step list.',
            );

            return;
        }

        if ($safeToDelete !== []) {
            $manualActions[] = 'These keys match the core defaults; delete them from your config: '.implode(', ', $safeToDelete).'.';
        }

        $context->report->record(
            $this->name(),
            $context->dryRun ? StepReport::STATUS_DRY_RUN : StepReport::STATUS_OK,
            "{$customised} customised and ".count($safeToDelete).' default config step list(s) to move.',
            $manualActions,
        );
    }

    /**
     * The list a published config file defines, with v1 class names mapped to v2,
     * or null when it does not define one.
     *
     * @return list<string>|null
     */
    protected function publishedList(StepList $list): ?array
    {
        [, $file, $path] = explode('.', $list->legacyConfigKey(), 3);

        $config = $this->publishedConfig[$file] ??= $this->loadPublishedConfig($file);

        if ($config === null || ! Arr::has($config, $path)) {
            return null;
        }

        return array_values(array_map(
            fn (string $class): string => LunarSetList::V1_TO_V2_CLASS_RENAMES[ltrim($class, '\\')] ?? ltrim($class, '\\'),
            (array) Arr::get($config, $path),
        ));
    }

    /**
     * Where the app's published Lunar config files live.
     */
    protected function configDirectory(): string
    {
        return config_path('lunar');
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function loadPublishedConfig(string $file): ?array
    {
        $configPath = $this->configDirectory()."/{$file}.php";

        if (! File::exists($configPath)) {
            return null;
        }

        $config = require $configPath;

        return is_array($config) ? $config : null;
    }

    /**
     * The `Steps::` calls that turn the core defaults into the configured list.
     *
     * @param  list<string>  $configured
     * @return list<string>
     */
    protected function registrationsFor(StepList $list, array $configured): array
    {
        $defaults = $this->manifest->defaults($list);
        $calls = [];

        foreach ($defaults as $step) {
            if (! in_array($step, $configured, true)) {
                $calls[] = "->remove(\\{$step}::class)";
            }
        }

        $previous = null;

        foreach ($configured as $step) {
            if (! in_array($step, $defaults, true)) {
                $calls[] = $previous === null
                    ? "->prepend(\\{$step}::class)"
                    : "->after(\\{$previous}::class, \\{$step}::class)";
            }

            $previous = $step;
        }

        $keptInDefaultOrder = array_values(array_intersect($defaults, $configured));
        $keptInConfiguredOrder = array_values(array_intersect($configured, $defaults));

        if ($keptInDefaultOrder !== $keptInConfiguredOrder) {
            $calls[] = '// Core steps are also reordered; re-create that order with remove() and after().';
        }

        return $calls;
    }
}
