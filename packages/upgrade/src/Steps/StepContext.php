<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Steps;

use Illuminate\Console\OutputStyle;
use Lunar\Upgrade\Support\StepReport;

class StepContext
{
    /**
     * @param  array<int, string>  $paths
     */
    public function __construct(
        public readonly bool $dryRun,
        public readonly array $paths,
        public readonly OutputStyle $output,
        public readonly StepReport $report,
        public readonly ?string $connection = null,
    ) {}
}
