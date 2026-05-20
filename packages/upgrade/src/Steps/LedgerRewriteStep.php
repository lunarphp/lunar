<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Steps;

use Lunar\Upgrade\Support\StepReport;

/**
 * Rewrites the application's `migrations` table to mark the v2 flat baseline
 * as already-applied after the schema transformation runs.
 *
 * The actual implementation lands with spec 0003 (flat migrations baseline);
 * the skeleton ships a no-op placeholder so the registry slot is reserved.
 */
class LedgerRewriteStep implements UpgradeStep
{
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
        $context->report->record(
            $this->name(),
            StepReport::STATUS_SKIPPED,
            'Ledger rewrite is implemented by spec 0003.',
            manualActions: [
                'Verify your `migrations` table matches the v2 baseline after spec 0003 lands.',
            ],
        );
    }
}
