<?php

namespace Lunar\Base;

use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Models\Contracts\CartLine;
use Lunar\Models\Contracts\Currency;
use Lunar\Models\Contracts\TaxZone;

interface TaxDriver
{
    /**
     * Set the shipping address.
     */
    public function setShippingAddress(?Addressable $address = null): self;

    /**
     * Set the currency.
     */
    public function setCurrency(Currency $currency): self;

    /**
     * Set the billing address.
     */
    public function setBillingAddress(?Addressable $address = null): self;

    /**
     * Set the purchasable item.
     */
    public function setPurchasable(Purchasable $purchasable): self;

    /**
     * Set the cart line.
     */
    public function setCartLine(CartLine $cartLine): self;

    /**
     * Set a tax zone override.
     *
     * When provided, this zone is used directly instead of resolving one from the shipping address, allowing
     * the developer to handle cases like taxation on IP address basis. Just set the tax zone as and let it
     * flow smoothly.
     */
    public function setTaxZone(?TaxZone $taxZone = null): self;

    /**
     * Return the tax breakdown from a given sub total.
     *
     * @param  int  $subTotal
     */
    public function getBreakdown($subTotal): TaxBreakdown;
}
