<?php

namespace Lunar\Bundles\Support;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CustomerGroup;

/**
 * The customer groups a cart is priced for, resolved the way core's
 * `GetUnitPrice` stage does it: the cart's customer first, else the user's
 * customers. The pricing manager falls back to the default group when empty.
 */
trait ResolvesCartCustomerGroups
{
    /** @return Collection<int, CustomerGroup> */
    protected function customerGroupsFor(Cart $cart): Collection
    {
        $cart->loadMissing('customer.customerGroups');

        if ($cart->customer) {
            return $cart->customer->customerGroups;
        }

        $cart->loadMissing('user');
        $cart->user?->loadMissing('customers.customerGroups');

        return collect($cart->user?->customers->pluck('customerGroups')->flatten());
    }
}
