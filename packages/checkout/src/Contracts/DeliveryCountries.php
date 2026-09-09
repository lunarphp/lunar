<?php

namespace Lunar\Checkout\Contracts;

use Illuminate\Support\Collection;
use Lunar\Checkout\DeliveryCountries\ConfiguredCountries;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Country;

/**
 * Supplies the countries a cart may be delivered to (spec 0011 §H). The
 * delivery step renders exactly this list and the shipping-address store
 * refuses anything outside it, so a customer never saves an address the
 * store cannot ship to and then finds no delivery options.
 *
 * The package binds {@see ConfiguredCountries}
 * by default; a shipping package that knows its zones rebinds this to derive
 * the list from them.
 */
interface DeliveryCountries
{
    /**
     * @return Collection<int, Country>
     */
    public function available(Cart $cart): Collection;
}
