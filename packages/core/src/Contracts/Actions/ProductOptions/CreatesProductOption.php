<?php

namespace Lunar\Core\Contracts\Actions\ProductOptions;

use Lunar\Core\Models\ProductOption;

interface CreatesProductOption
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): ProductOption;
}
