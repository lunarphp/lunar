<?php

namespace Lunar\Base;

use Illuminate\Support\Collection;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Cart;

interface ShippingManifestInterface
{
    /**
     * Add a shipping option to the manifest.
     *
     * @param  \Lunar\DataTypes\ShippingOption  $shippingOption
     * @return self
     */
    public function addOption(ShippingOption $shippingOption);

    /**
     * Return available options for a given cart.
     *
     * @param  \Lunar\Models\Cart  $cart
     * @return \Illuminate\Support\Collection
     */
    public function getOptions(Cart $cart): Collection;
}
