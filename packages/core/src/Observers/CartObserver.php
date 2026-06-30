<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Events\Carts\CartCreated;
use Lunar\Core\Events\Carts\CartDeleted;
use Lunar\Core\Models\Cart;

class CartObserver
{
    public function created(Cart $cart): void
    {
        CartCreated::dispatch($cart);
    }

    public function deleted(Cart $cart): void
    {
        CartDeleted::dispatch($cart);
    }
}
