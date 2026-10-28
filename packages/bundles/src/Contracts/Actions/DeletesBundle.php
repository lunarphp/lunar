<?php

namespace Lunar\Bundles\Contracts\Actions;

use Lunar\Bundles\Models\Bundle;

interface DeletesBundle
{
    /**
     * Remove the bundle definition. The variant becomes an ordinary variant
     * again; its materialised prices are left in place.
     */
    public function execute(Bundle $bundle): void;
}
