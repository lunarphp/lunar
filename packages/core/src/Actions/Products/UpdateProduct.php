<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Contracts\Actions\Products\UpdatesProduct;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Product;

/**
 * Update a product's attributes and, when given, sync its tags, collection
 * membership, and channel/customer-group availability pivots — a full set
 * replaces the current one, while null leaves that surface untouched.
 * Variant, price and option changes are not this action's job; see the
 * variant actions and GenerateProductVariants.
 */
class UpdateProduct implements UpdatesProduct
{
    public function execute(
        Product $product,
        array $attributes,
        ?array $tags = null,
        ?array $collectionIds = null,
        ?array $channels = null,
        ?array $customerGroups = null,
    ): Product {
        return DB::transaction(function () use ($product, $attributes, $tags, $collectionIds, $channels, $customerGroups): Product {
            $product->update($attributes);

            if ($tags !== null) {
                $product->syncTags(collect($tags));
            }

            if ($collectionIds !== null) {
                $product->collections()->sync($collectionIds);
            }

            if ($channels !== null) {
                $product->channels()->sync($channels);
            }

            if ($customerGroups !== null) {
                $product->customerGroups()->sync($customerGroups);
            }

            return $product;
        });
    }
}
