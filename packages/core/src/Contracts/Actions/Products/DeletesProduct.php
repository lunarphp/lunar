<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\Product;

interface DeletesProduct
{
    public function execute(Product $product): void;
}
