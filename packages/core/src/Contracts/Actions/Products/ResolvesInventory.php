<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\DataObjects\VariantInventory;
use Lunar\Core\Models\ProductVariant;

interface ResolvesInventory
{
    /**
     * Answer how many units of the variant can be sold right now.
     *
     * `ProductVariant::getTotalInventory()` and `canBeFulfilledAtQuantity()`
     * delegate here, so a package that derives a variant's availability from
     * elsewhere (a bundle from its components, say) decorates this binding.
     */
    public function execute(ProductVariant $variant): VariantInventory;
}
