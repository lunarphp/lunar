<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Models\Contracts\Cart as CartContract;

interface SetsShippingOption
{
    public function execute(
        CartContract $cart,
        ShippingOption $shippingOption
    ): void;
}
