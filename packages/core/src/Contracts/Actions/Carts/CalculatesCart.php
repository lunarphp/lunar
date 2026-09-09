<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Models\Cart;

interface CalculatesCart
{
    /**
     * Populate the cart's totals, serving a fresh persisted snapshot when one
     * exists and running the calculation pipeline otherwise. `$force` always
     * runs the pipeline.
     */
    public function execute(Cart $cart, bool $force = false): Cart;
}
