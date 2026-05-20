<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Console;

use Illuminate\Console\Command;
use Lunar\Upgrade\Exceptions\UpgradeAbortedException;
use Lunar\Upgrade\Steps\StepContext;
use Lunar\Upgrade\Steps\StepRegistry;
use Lunar\Upgrade\Steps\UpgradeStep;
use Lunar\Upgrade\Support\SchemaGuard;
use Lunar\Upgrade\Support\StepReport;
use Lunar\Upgrade\Support\VersionGuard;

class UpgradeCommand extends Command
{
    protected $signature = 'lunar:upgrade
        {--dry-run : Print what each step would do without writing changes}
        {--only=* : Run only the named step(s)}
        {--skip=* : Skip the named step(s)}
        {--paths=* : Override the user code paths Rector runs against}
        {--connection= : Database connection to operate on}';

    protected $description = 'Migrate a Lunar v1.x application to v2.x.';

    public function handle(
        VersionGuard $versionGuard,
        SchemaGuard $schemaGuard,
        StepRegistry $registry,
    ): int {
        try {
            $versionGuard->assertLatestV1();
            $schemaGuard->assertV1MigrationsApplied($this->option('connection'));

            $steps = $registry->resolve(
                (array) $this->option('only'),
                (array) $this->option('skip'),
            );
        } catch (UpgradeAbortedException $e) {
            $this->components->error($e->getMessage());

            if ($e->remediation !== null) {
                $this->components->info($e->remediation);
            }

            return self::FAILURE;
        }

        $report = new StepReport;
        $context = new StepContext(
            dryRun: (bool) $this->option('dry-run'),
            paths: (array) $this->option('paths'),
            output: $this->output,
            report: $report,
            connection: $this->option('connection'),
        );

        $this->printPlan($steps);

        foreach ($steps as $index => $step) {
            $number = $index + 1;
            $this->components->task("[{$number}/".count($steps)."] {$step->label()}", function () use ($step, $context): bool {
                $step->run($context);

                return true;
            });
        }

        $this->printReport($report);

        return self::SUCCESS;
    }

    /**
     * @param  array<int, UpgradeStep>  $steps
     */
    protected function printPlan(array $steps): void
    {
        if ($steps === []) {
            $this->components->warn('No upgrade steps registered.');

            return;
        }

        $this->components->info('Upgrade plan:');

        foreach ($steps as $index => $step) {
            $number = $index + 1;
            $this->line("  {$number}. {$step->label()} <fg=gray>({$step->specReference()})</>");
        }

        $this->newLine();
    }

    protected function printReport(StepReport $report): void
    {
        $rows = $report->rows();

        if ($rows === []) {
            return;
        }

        $this->newLine();
        $this->table(
            ['Step', 'Status', 'Summary'],
            array_map(fn (array $row): array => [$row['step'], $row['status'], $row['summary']], $rows),
        );

        $manualActions = $report->manualActions();

        if ($manualActions === []) {
            return;
        }

        $this->newLine();
        $this->components->info('Manual actions required:');

        foreach ($manualActions as $action) {
            $this->line("  - {$action}");
        }
    }
}
