<?php

namespace Lunar\Core\Contracts\Actions\Discounts;

use Lunar\Core\Models\Discount;

interface CreatesDiscount
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Discount;
}
