<?php

namespace Lunar\Bundles\Contracts\Actions;

use Lunar\Bundles\Exceptions\BundleNesting;
use Lunar\Bundles\Exceptions\InvalidBundleDefinition;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleGroup;
use Lunar\Core\Models\ProductVariant;

interface SyncsBundleComponents
{
    /**
     * Upsert and delete components so the bundle matches `$components`, then reprice.
     *
     * @param  list<array{variant: ProductVariant|int, quantity: int, group?: BundleGroup|int|null, default?: bool, position?: int}>  $components
     *
     * @throws BundleNesting
     * @throws InvalidBundleDefinition
     */
    public function execute(Bundle $bundle, array $components): Bundle;
}
