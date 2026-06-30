<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\Product;

interface DuplicatesProduct
{
    public function execute(Product $source, ?string $nameSuffix = null): Product;
}
