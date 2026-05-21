<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Steps;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\File;
use Lunar\Upgrade\Support\StepReport;

class DataMigrationStep implements UpgradeStep
{
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
        );
    }
}
