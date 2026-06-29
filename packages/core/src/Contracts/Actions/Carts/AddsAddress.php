<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Contracts\Addressable;
use Lunar\Core\Models\Cart;

interface AddsAddress
{
    public function execute(
        Cart $cart,
        array|Addressable $address,
        string $type
    ): void;
}
