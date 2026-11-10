<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Models\CartAddress;

/**
 * Address fields, the shipping option and the tax identifier all feed the
 * cart's totals, so any address write stales the persisted snapshot.
 */
class CartAddressObserver
{
    public function saved(CartAddress $cartAddress): void
    {
        $cartAddress->cart?->invalidateTotals();
    }

    public function deleted(CartAddress $cartAddress): void
    {
        $cartAddress->cart?->invalidateTotals();
    }
}
