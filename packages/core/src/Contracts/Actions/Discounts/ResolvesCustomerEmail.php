<?php

namespace Lunar\Core\Contracts\Actions\Discounts;

use Lunar\Core\Models\Cart;

interface ResolvesCustomerEmail
{
    /**
     * The email that identifies the cart's customer for per-customer discount limits.
     */
    public function execute(Cart $cart): ?string;
}
