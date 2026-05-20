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
        $renameCount = count(LunarSetList::V1_TO_V2_CLASS_RENAMES);
        $extraRules = count(LunarSetList::V1_TO_V2);

        if ($renameCount === 0 && $extraRules === 0) {
            $context->report->record(
                $this->name(),
                StepReport::STATUS_SKIPPED,
                'No Rector rules registered yet.',
            );

            return;
        }

        // Future specs land the actual `rector process` invocation here. The
        // skeleton records what would run; users invoke the bundled
        // `config/rector.php` against their app directly until then.
        $context->report->record(
            $this->name(),
            $context->dryRun ? StepReport::STATUS_DRY_RUN : StepReport::STATUS_OK,
            "Would rewrite {$renameCount} class reference(s) across ".count($context->paths).' path(s).',
        );
    }
}
