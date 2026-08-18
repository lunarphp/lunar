<?php

namespace Lunar\Core\Actions\Brands;

use Lunar\Core\Contracts\Actions\Brands\UpdatesBrand;
use Lunar\Core\Models\Brand;

/**
 * Update a brand's attributes and, when a collection set is given, sync it
 * to those collections — an empty set clears any current collections, while
 * null leaves collection membership untouched.
 */
class UpdateBrand implements UpdatesBrand
{
    public function execute(Brand $brand, array $attributes, ?array $collectionIds = null): Brand
    {
        $brand->update($attributes);

        if ($collectionIds !== null) {
            $brand->collections()->sync($collectionIds);
        }

        return $brand;
    }
}
