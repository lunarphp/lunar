<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Steps;

use Lunar\Upgrade\Rector\LunarSetList;
use Lunar\Upgrade\Support\StepReport;

class RectorStep implements UpgradeStep
{
    public function name(): string
    {
        return 'rector';
    }

    public function label(): string
    {
        return 'Rewrite v1.x code with Rector';
    }

    public function specReference(): string
    {
        return '0001-upgrade-package';
    }

    public function run(StepContext $context): void
    {
        if (LunarSetList::V1_TO_V2 === []) {
            $context->report->record(
                $this->name(),
                StepReport::STATUS_SKIPPED,
                'No Rector rules registered yet.',
            );

            return;
        }

        // Future specs land their invocation here. The skeleton intentionally
        // does not shell out to `rector process` until at least one rule exists.
        $context->report->record(
            $this->name(),
            $context->dryRun ? StepReport::STATUS_DRY_RUN : StepReport::STATUS_OK,
            'Would run '.count(LunarSetList::V1_TO_V2).' Rector rule(s) against '.count($context->paths).' path(s).',
        );
    }
}
