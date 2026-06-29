<?php

namespace Lunar\Core\Drivers;

use Lunar\Core\Contracts\Addressable;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\TaxZone;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;

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
