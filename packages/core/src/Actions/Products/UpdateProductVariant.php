<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Contracts\Actions\Products\UpdatesProductVariant;
use Lunar\Core\Models\ProductVariant;

/**
 * Update a variant's own fields (identifiers, tax, shipping, ordering
 * quantities, selling policy, enabled flag, attribute data). Prices, stock
 * and option values have their own seams.
 */
class UpdateProductVariant implements UpdatesProductVariant
{
    public function execute(ProductVariant $variant, array $attributes): ProductVariant
    {
        $variant->update($attributes);

        return $variant;
    }
}
