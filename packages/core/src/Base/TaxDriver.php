<?php

namespace Lunar\Base;

use Lunar\Models\CartLine;
use Lunar\Models\Currency;
use Lunar\ValueObjects\Cart\TaxBreakdown;

interface TaxDriver
{
    /**
     * Set the shipping address.
     *
     * @param  \Lunar\Base\Addressable|null  $address
     * @return self
     */
    public function setShippingAddress(Addressable $address = null): self;

    /**
     * Set the currency.
     *
     * @param  \Lunar\Models\Currency  $currency
     * @return self
     */
    public function setCurrency(Currency $currency): self;

    /**
     * Set the billing address.
     *
     * @param  \Lunar\Base\Addressable|null  $address
     * @return self
     */
    public function setBillingAddress(Addressable $address = null): self;

    /**
     * Set the purchasable item.
     *
     * @param  \Lunar\Base\Purchasable  $purchasable
     * @return self
     */
    public function setPurchasable(Purchasable $purchasable): self;

    /**
     * Set the cart line.
     *
     * @param  \Lunar\Models\CartLine  $cartLine
     * @return self
     */
    public function setCartLine(CartLine $cartLine): self;

    /**
     * Return the tax breakdown from a given sub total.
     *
     * @param  int  $subTotal
     * @return \Lunar\ValueObjects\Cart\TaxBreakdown
     */
    public function getBreakdown($subTotal): TaxBreakdown;
}
