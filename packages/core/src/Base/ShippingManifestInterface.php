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
     * @return self
     */
    public function addOption(ShippingOption $shippingOption);

    /**
     * Add a collection of shipping options to the manifest.
     *
     * @param  \Lunar\DataTypes\ShippingOption  $shippingOption
     * @return self
     */
    public function addOptions(Collection $shippingOptions);

    /**
     * Remove all shipping options
     *
     * @return self
     */
    public function clearOptions();

    /**
     * Return available options for a given cart.
     */
    public function getOptions(Cart $cart): Collection;
}
