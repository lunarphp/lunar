<?php

namespace Lunar\DemoData\Generators;

use Lunar\DemoData\Support\DemoContext;

interface Generator
{
    /**
     * Generate this domain's records. Implementations must be idempotent
     * against their own data so partial reseeds are cheap.
     */
    public function generate(DemoContext $context): void;
}
