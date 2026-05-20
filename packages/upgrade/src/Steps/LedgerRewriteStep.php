<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Steps;

use Illuminate\Database\ConnectionResolverInterface;
use Lunar\Upgrade\Support\StepReport;

/**
 * Rewrites the application's `migrations` ledger so that v1 Lunar migrations
 * are removed and the v2 flat baseline (see spec 0003) is marked as run.
 *
 * The actual v2 baseline filenames are configured under
 * `lunar.upgrade.ledger.v2_baseline`. The skeleton ships an empty baseline
 * list until the flattened migrations land in `packages/core/database/migrations`.
 */
class LedgerRewriteStep implements UpgradeStep
{
    public function __construct(protected ConnectionResolverInterface $connections) {}

    public function name(): string
    {
        return 'ledger-rewrite';
    }

    public function label(): string
    {
        return 'Rewrite migrations ledger to v2 baseline';
    }

    public function specReference(): string
    {
        return '0003-flatten-migrations';
    }

    public function run(StepContext $context): void
    {
        /** @var array{v1_match: array<int, string>, v2_baseline: array<int, string>} $config */
        $config = config('lunar.upgrade.ledger', ['v1_match' => [], 'v2_baseline' => []]);

        if ($config['v1_match'] === [] && $config['v2_baseline'] === []) {
            $context->report->record(
                $this->name(),
                StepReport::STATUS_SKIPPED,
                'No ledger configuration set; nothing to rewrite.',
            );

            return;
        }

        $connection = $this->connections->connection($context->connection);

        $existing = $connection->table('migrations')->pluck('migration')->all();

        $toRemove = array_values(array_filter(
            $existing,
            fn (string $migration): bool => $this->matchesAny($migration, $config['v1_match']),
        ));

        $toInsert = array_values(array_diff($config['v2_baseline'], $existing));

        if ($context->dryRun) {
            $context->report->record(
                $this->name(),
                StepReport::STATUS_DRY_RUN,
                'Would remove '.count($toRemove).' v1 ledger row(s) and insert '.count($toInsert).' v2 baseline row(s).',
            );

            return;
        }

        $batch = (int) ($connection->table('migrations')->max('batch') ?? 0) + 1;

        $connection->transaction(function () use ($connection, $toRemove, $toInsert, $batch): void {
            if ($toRemove !== []) {
                $connection->table('migrations')->whereIn('migration', $toRemove)->delete();
            }

            if ($toInsert !== []) {
                $connection->table('migrations')->insert(array_map(
                    fn (string $migration): array => ['migration' => $migration, 'batch' => $batch],
                    $toInsert,
                ));
            }
        });

        $context->report->record(
            $this->name(),
            StepReport::STATUS_OK,
            'Removed '.count($toRemove).' v1 row(s); inserted '.count($toInsert).' v2 baseline row(s).',
        );
    }

    /**
     * @param  array<int, string>  $patterns
     */
    protected function matchesAny(string $migration, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $migration) === 1) {
                return true;
            }
        }

        return false;
    }
}
