<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\Contracts\Product as ProductContract;
use Lunar\Core\Models\Product;

interface UpdatesProductStatus
{
    public function execute(ProductContract $product, string $status): Product;
}
