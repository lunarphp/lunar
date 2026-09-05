<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Steps;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\File;
use Lunar\Upgrade\Support\StepReport;

class DataMigrationStep implements UpgradeStep
{
    /**
     * Changes the data migrations cannot finish on the consumer's behalf.
     *
     * The AmountOff split is the one breaking change in v2's discount types that
     * gets no Rector rule: which of the two replacements a code reference means
     * depends on the discount's runtime `data.fixed_value`, so a rename rule would
     * be wrong about half the time. Stored rows are converted by
     * 2026_06_01_000015; source references are the consumer's to resolve.
     *
     * @var array<int, string>
     */
    public const MANUAL_ACTIONS = [
        'Lunar\\DiscountTypes\\AmountOff has been split into Lunar\\Core\\DiscountTypes\\PercentageOff '
            .'and Lunar\\Core\\DiscountTypes\\FixedAmountOff. Your discount records have been converted; '
            .'update any code that references AmountOff to whichever type it meant. Its data.fixed_values key '
            .'is now data.amounts, and the data.fixed_value flag has been removed.',
    ];

    public function __construct(protected Migrator $migrator) {}

    public function name(): string
    {
        return 'data-migrations';
    }

    public function label(): string
    {
        return 'Run v1 → v2 data migrations';
    }

    public function specReference(): string
    {
        return '0001-upgrade-package';
    }

    public function run(StepContext $context): void
    {
        $directory = dirname(__DIR__, 2).'/database/migrations';

        $files = File::isDirectory($directory)
            ? collect(File::files($directory))
                ->filter(fn ($file): bool => str_ends_with($file->getFilename(), '.php'))
                ->values()
            : collect();

        if ($files->isEmpty()) {
            $context->report->record(
                $this->name(),
                StepReport::STATUS_SKIPPED,
                'No data migrations registered yet.',
            );

            return;
        }

        if ($context->dryRun) {
            $context->report->record(
                $this->name(),
                StepReport::STATUS_DRY_RUN,
                "Would run {$files->count()} data migration(s).",
                self::MANUAL_ACTIONS,
            );

            return;
        }

        if ($context->connection !== null) {
            $this->migrator->setConnection($context->connection);
        }

        $this->migrator->run([$directory]);

        $context->report->record(
            $this->name(),
            StepReport::STATUS_OK,
            "Ran {$files->count()} data migration(s).",
            self::MANUAL_ACTIONS,
        );
    }
}
