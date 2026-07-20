<?php

namespace Lunar\Core\Actions\Brands;

use Lunar\Core\Contracts\Actions\Brands\CreatesBrand;
use Lunar\Core\Models\Brand;

/**
 * Create a brand and, when given, sync it to the requested collections.
 * A missing handle is generated from the name by the model's creating hook.
 */
class CreateBrand implements CreatesBrand
{
    public function execute(array $attributes, ?array $collectionIds = null): Brand
    {
        $brand = Brand::create($attributes);

        if ($collectionIds !== null) {
            $brand->collections()->sync($collectionIds);
        }

        return $brand;
    }
}
