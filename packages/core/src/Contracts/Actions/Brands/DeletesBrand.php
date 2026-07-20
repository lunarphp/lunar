<?php

namespace Lunar\Core\Contracts\Actions\Brands;

use Lunar\Core\Exceptions\BrandActionException;
use Lunar\Core\Models\Brand;

interface DeletesBrand
{
    /**
     * @throws BrandActionException when the brand still has products
     */
    public function execute(Brand $brand): void;
}
