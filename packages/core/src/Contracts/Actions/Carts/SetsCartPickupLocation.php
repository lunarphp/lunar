<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Location;

interface SetsCartPickupLocation
{
    public function execute(Cart $cart, ?Location $location): void;
}
