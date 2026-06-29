<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\Product;

interface UpdatesProductStatus
{
    public function execute(Product $product, string $status): Product;
}
