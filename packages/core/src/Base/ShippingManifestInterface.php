<?php

namespace GetCandy\Base;

use GetCandy\DataTypes\ShippingOption;
use GetCandy\Models\Cart;
use Illuminate\Support\Collection;

interface ShippingManifestInterface
{
    /**
     * Add a shipping option to the manifest.
     *
     * @param \GetCandy\DataTypes\ShippingOption $option
     *
     * @return self
     */
    public function addOption(ShippingOption $shippingOption);

    /**
     * Return available options for a given cart.
     *
     * @param \GetCandy\Models\Cart $cart
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOptions(Cart $cart): Collection;
}
