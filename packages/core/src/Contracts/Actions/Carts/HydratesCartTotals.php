<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Models\Cart;

interface HydratesCartTotals
{
    /**
     * Rebuild the cart's and its lines' calculated properties from the
     * persisted totals columns.
     */
    public function execute(Cart $cart): Cart;
}
