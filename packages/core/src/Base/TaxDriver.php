<?php

namespace GetCandy\Base;

use GetCandy\Base\DataTransferObjects\TaxBreakdown;
use GetCandy\Models\CartLine;
use GetCandy\Models\Currency;
use Illuminate\Support\Collection;

interface TaxDriver
{
    /**
     * Set the shipping address.
     *
     * @param  \GetCandy\Base\Addressable|null  $address
     * @return self
     */
    public function setShippingAddress(Addressable $address = null): self;

    /**
     * Set the currency.
     *
     * @param  \GetCandy\Models\Currency  $currency
     * @return self
     */
    public function setCurrency(Currency $currency): self;

    /**
     * Set the billing address.
     *
     * @param  \GetCandy\Base\Addressable|null  $address
     * @return self
     */
    public function setBillingAddress(Addressable $address = null): self;

    /**
     * Set the purchasable item.
     *
     * @param  \GetCandy\Base\Purchasable  $purchasable
     * @return self
     */
    public function setPurchasable(Purchasable $purchasable): self;

    /**
     * Set the cart line.
     *
     * @param \GetCandy\Models\CartLine $cartLine
     * @return self
     */
    public function setCartLine(CartLine $cartLine): self;

    /**
     * Return the tax breakdown from a given sub total.
     *
     * @param  int  $subTotal
     *
     * @return \GetCandy\Base\DataTransferObjects\TaxBreakdown
     */
    public function getBreakdown($subTotal): TaxBreakdown;
}
