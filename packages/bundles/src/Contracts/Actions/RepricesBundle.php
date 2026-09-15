<?php

namespace Lunar\Bundles\Contracts\Actions;

use Lunar\Bundles\Models\Bundle;

interface RepricesBundle
{
    /**
     * Materialise price rows on the bundle variant from its default selection.
     * A no-op for fixed pricing.
     */
    public function execute(Bundle $bundle): Bundle;
}
