<?php

namespace Lunar\Core\Contracts\Actions\ProductTypes;

use Lunar\Core\Exceptions\ProductTypeActionException;
use Lunar\Core\Models\ProductType;

interface DeletesProductType
{
    /**
     * @throws ProductTypeActionException when the product type still has products
     */
    public function execute(ProductType $productType): void;
}
