<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Models\Cart;

interface SetsShippingOption
{
    public function execute(
        Cart $cart,
        ShippingOption $shippingOption
    ): void;
}
