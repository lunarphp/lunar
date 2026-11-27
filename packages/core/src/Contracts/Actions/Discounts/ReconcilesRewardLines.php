<?php

namespace Lunar\Core\Contracts\Actions\Discounts;

use Lunar\Core\Models\Cart;

interface ReconcilesRewardLines
{
    /**
     * Create, resize or delete the cart's lines a discount added so each
     * holds the reward units its discount allocated to it in the calculation
     * just run. Returns whether any line was written.
     */
    public function execute(Cart $cart): bool;
}
