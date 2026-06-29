<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Models\Cart;

interface MergesCart
{
    public function execute(Cart $target, Cart $source): Cart;
}
