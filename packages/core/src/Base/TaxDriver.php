<?php

namespace GetCandy\Base;

use GetCandy\Models\Currency;

interface TaxDriver
{
    /**
     * Set the shipping address.
     *
     * @param  \GetCandy\Base\Addressable|null  $address
     * @return self
     */
    public function setShippingAddress(Addressable $address = null);

    /**
     * Set the currency.
     *
     * @param  \GetCandy\Models\Currency  $currency
     * @return self
     */
    public function setCurrency(Currency $currency);

    /**
     * Set the billing address.
     *
     * @param  \GetCandy\Base\Addressable|null  $address
     * @return self
     */
    public function setBillingAddress(Addressable $address = null);

    /**
     * Set the purchasable item.
     *
     * @param  \GetCandy\Base\Purchasable|null  $address
     * @return self
     */
    public function setPurchasable(Purchasable $purchasable);

    /**
     * Return the tax breakdown from a given sub total.
     *
     * @param  int  $subTotal
     */
    public function getBreakdown($subTotal);
}
