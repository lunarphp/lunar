<?php

namespace Lunar\Core\Actions\ProductOptions;

use Lunar\Core\Contracts\Actions\ProductOptions\CreatesProductOption;
use Lunar\Core\Models\ProductOption;

class CreateProductOption implements CreatesProductOption
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): ProductOption
    {
        return ProductOption::create($attributes);
    }
}
