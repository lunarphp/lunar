<?php

namespace Lunar\Core\Events\Carts;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Models\Cart;

class CartCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Cart $cart,
    ) {}
}
