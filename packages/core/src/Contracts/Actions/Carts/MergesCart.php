<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Contracts\Cart as CartContract;

interface MergesCart
{
    public function execute(CartContract $target, Cart $source): CartContract;
}
