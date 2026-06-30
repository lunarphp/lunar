<?php

namespace Lunar\Core\Events\Carts;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Models\Cart;

/**
 * A source cart was merged into a target cart (e.g. a guest cart folded into the
 * authenticated customer's cart on login).
 */
class CartMerged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Cart $target,
        public Cart $source,
    ) {}
}
