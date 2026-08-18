<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\Product;

interface CreatesProduct
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Product;
}
