<?php

namespace Lunar\Core\Contracts\Actions\ProductTypes;

use Lunar\Core\Models\ProductType;

interface UpdatesProductType
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  ?array<int, int>  $attributeIds  null leaves the product/variant attribute mapping untouched; an empty array clears it
     */
    public function execute(ProductType $productType, array $attributes, ?array $attributeIds = null): ProductType;
}
