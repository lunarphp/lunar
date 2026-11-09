<?php

namespace Lunar\Bundles\Contracts\Actions;

use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Exceptions\BundleNesting;
use Lunar\Bundles\Models\Bundle;
use Lunar\Core\Models\ProductVariant;

interface DefinesBundle
{
    /**
     * Create or update the bundle definition on a variant, then reprice it.
     *
     * @throws BundleNesting when the variant is already a component of another bundle
     */
    public function execute(ProductVariant $variant, BundlePricing $pricing, ?float $discountPercentage = null): Bundle;
}
