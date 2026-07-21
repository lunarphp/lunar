<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\ProductVariant;

interface UpdatesProductVariant
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(ProductVariant $variant, array $attributes): ProductVariant;
}
