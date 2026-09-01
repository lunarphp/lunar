<?php

namespace Lunar\Core\Actions\Discounts;

use Lunar\Core\Contracts\Actions\Discounts\CreatesDiscount;
use Lunar\Core\Models\Discount;

/**
 * Create a discount. Availability and targeting are configured afterwards
 * through UpdatesDiscount, which is where the fan-out across the four
 * targeting tables lives.
 */
class CreateDiscount implements CreatesDiscount
{
    public function execute(array $attributes): Discount
    {
        return Discount::create($attributes);
    }
}
