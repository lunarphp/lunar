<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Contracts\Addressable;
use Lunar\Core\Models\Contracts\Cart as CartContract;

interface AddsAddress
{
    public function execute(
        CartContract $cart,
        array|Addressable $address,
        string $type
    ): void;
}
