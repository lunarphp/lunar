<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Enums\Concerns\ProvidesProductAssociationType;
use Lunar\Core\Models\Product;

interface SyncsProductAssociations
{
    /**
     * Make the product's associations of one type exactly the given targets,
     * in the given order.
     *
     * @param  array<int, int>  $targetIds
     */
    public function execute(Product $product, ProvidesProductAssociationType|string $type, array $targetIds): Product;
}
