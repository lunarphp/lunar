<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\ProductVariant;

interface DeletesProductVariant
{
    public function execute(ProductVariant $variant): void;
}
