<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Models\Cart;

interface PersistsCartTotals
{
    /**
     * Write the cart's calculated properties to the totals columns, guarded on
     * the revision the cart was loaded with. Returns false when the cart
     * changed underneath the calculation and the snapshot was skipped.
     */
    public function execute(Cart $cart): bool;
}
