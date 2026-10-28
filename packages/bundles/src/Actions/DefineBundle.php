<?php

namespace Lunar\Bundles\Actions;

use Lunar\Bundles\Contracts\Actions\DefinesBundle;
use Lunar\Bundles\Contracts\Actions\RepricesBundle;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Exceptions\BundleNesting;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Core\Models\ProductVariant;

class DefineBundle implements DefinesBundle
{
    public function __construct(
        protected RepricesBundle $reprice,
    ) {}

    public function execute(ProductVariant $variant, BundlePricing $pricing, ?float $discountPercentage = null): Bundle
    {
        if (BundleComponent::query()->where('product_variant_id', $variant->getKey())->exists()) {
            throw BundleNesting::isComponent();
        }

        $bundle = Bundle::query()->firstOrNew(['product_variant_id' => $variant->getKey()]);

        $bundle->fill([
            'pricing' => $pricing,
            'discount_percentage' => $pricing === BundlePricing::Components ? $discountPercentage : null,
        ])->save();

        $bundle->setRelation('variant', $variant);

        return $this->reprice->execute($bundle);
    }
}
