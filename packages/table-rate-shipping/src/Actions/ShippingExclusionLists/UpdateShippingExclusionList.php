<?php

namespace Lunar\Shipping\Actions\ShippingExclusionLists;

use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Product;
use Lunar\Shipping\Contracts\Actions\ShippingExclusionLists\UpdatesShippingExclusionList;
use Lunar\Shipping\Models\ShippingExclusionList;

/**
 * Update an exclusion list and, when supplied, replace its excluded products.
 *
 * `products` is a list of product ids. Exclusions are stored as a purchasable
 * morph; this replaces the Product rows only and leaves any other morph type
 * a consumer may have written in place.
 */
class UpdateShippingExclusionList implements UpdatesShippingExclusionList
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(ShippingExclusionList $shippingExclusionList, array $attributes): ShippingExclusionList
    {
        $products = $attributes['products'] ?? null;

        unset($attributes['products']);

        DB::transaction(function () use ($shippingExclusionList, $attributes, $products): void {
            $shippingExclusionList->update($attributes);

            if ($products !== null) {
                $morph = Product::morphName();

                $shippingExclusionList->exclusions()
                    ->where('purchasable_type', $morph)
                    ->whereNotIn('purchasable_id', $products)
                    ->delete();

                $existing = $shippingExclusionList->exclusions()
                    ->where('purchasable_type', $morph)
                    ->pluck('purchasable_id');

                $shippingExclusionList->exclusions()->createMany(
                    collect($products)->diff($existing)->map(fn ($id) => [
                        'purchasable_type' => $morph,
                        'purchasable_id' => $id,
                    ])->all()
                );
            }
        });

        return $shippingExclusionList;
    }
}
