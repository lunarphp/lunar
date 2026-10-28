<?php

namespace Lunar\Bundles\Contracts\Actions;

use Lunar\Bundles\Exceptions\InvalidBundleSelection;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\ValueObjects\BundleSelection;

interface ResolvesBundleSelection
{
    /**
     * Turn cart line meta into a validated component list.
     *
     * @param  array<string, mixed>  $meta
     *
     * @throws InvalidBundleSelection
     */
    public function execute(Bundle $bundle, array $meta = []): BundleSelection;
}
