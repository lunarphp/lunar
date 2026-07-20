<?php

namespace Lunar\Core\Contracts\Actions\ProductOptions;

use Lunar\Core\Models\ProductOption;

interface DeletesProductOption
{
    public function execute(ProductOption $productOption): void;
}
