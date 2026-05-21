<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Steps;

interface UpgradeStep
{
    /**
     * Stable identifier used for --only / --skip filtering.
     */
    public function name(): string;

    /**
     * Human-readable label shown in the plan output.
     */
    public function label(): string;

    /**
     * Spec slug this step implements (e.g. "0002-core-namespace").
     */
    public function specReference(): string;

    public function run(StepContext $context): void;
}
