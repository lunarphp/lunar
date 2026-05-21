<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Steps;

use Illuminate\Contracts\Container\Container;
use Lunar\Upgrade\Exceptions\UpgradeAbortedException;

class StepRegistry
{
    /**
     * Ordered list of step classes executed by `lunar:upgrade`.
     *
     * Specs append their step here in the order it should run.
     *
     * @var array<int, class-string<UpgradeStep>>
     */
    public const STEPS = [
        RectorStep::class,
        DataMigrationStep::class,
        LedgerRewriteStep::class,
    ];

    public function __construct(protected Container $container) {}

    /**
     * @return array<int, UpgradeStep>
     */
    public function all(): array
    {
        return array_map(fn (string $class): UpgradeStep => $this->container->make($class), static::STEPS);
    }

    /**
     * @param  array<int, string>  $only
     * @param  array<int, string>  $skip
     * @return array<int, UpgradeStep>
     */
    public function resolve(array $only = [], array $skip = []): array
    {
        $steps = $this->all();
        $names = array_map(fn (UpgradeStep $step): string => $step->name(), $steps);

        foreach (array_merge($only, $skip) as $requested) {
            if (! in_array($requested, $names, true)) {
                throw new UpgradeAbortedException(
                    "Unknown upgrade step: {$requested}",
                    'Run `php artisan lunar:upgrade --help` to see available steps.',
                );
            }
        }

        return array_values(array_filter($steps, function (UpgradeStep $step) use ($only, $skip): bool {
            if ($only !== [] && ! in_array($step->name(), $only, true)) {
                return false;
            }

            return ! in_array($step->name(), $skip, true);
        }));
    }
}
