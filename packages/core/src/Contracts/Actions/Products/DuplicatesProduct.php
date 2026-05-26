<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\Contracts\Product as ProductContract;
use Lunar\Core\Models\Product;

interface DuplicatesProduct
{
    public function execute(ProductContract $source, ?string $nameSuffix = null): Product;
}
