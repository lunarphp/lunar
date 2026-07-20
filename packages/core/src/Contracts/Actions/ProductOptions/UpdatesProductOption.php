<?php

namespace Lunar\Core\Contracts\Actions\ProductOptions;

use Lunar\Core\Models\ProductOption;

interface UpdatesProductOption
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(ProductOption $productOption, array $attributes): ProductOption;
}
