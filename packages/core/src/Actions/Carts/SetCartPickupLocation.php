<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Contracts\Actions\Carts\SetsCartPickupLocation;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Location;

class SetCartPickupLocation implements SetsCartPickupLocation
{
    public function execute(Cart $cart, ?Location $location): void
    {
        /** @var Cart $cart */
        $cart->pickupLocation()->associate($location)->save();
    }
}
