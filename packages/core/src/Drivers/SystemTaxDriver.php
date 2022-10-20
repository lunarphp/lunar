<?php

namespace Lunar\Drivers;

use Lunar\Actions\Taxes\GetTaxZone;
use Lunar\Base\Addressable;
use Lunar\Base\Purchasable;
use Lunar\Base\TaxDriver;
use Lunar\Base\ValueObjects\TaxBreakdown;
use Lunar\Base\ValueObjects\TaxBreakdownAmount;
use Lunar\DataTypes\Price;
use Lunar\Models\CartLine;
use Lunar\Models\Currency;

class SystemTaxDriver implements TaxDriver
{
    /**
     * The taxable shipping address.
     *
     * @var \Lunar\Base\Addressable|null
     */
    protected ?Addressable $shippingAddress = null;

    /**
     * The taxable billing address.
     *
     * @var \Lunar\Base\Addressable|null
     */
    protected ?Addressable $billingAddress = null;

    /**
     * The currency model.
     *
     * @var Currency
     */
    protected Currency $currency;

    /**
     * The purchasable item.
     *
     * @var Purchasable
     */
    protected Purchasable $purchasable;

    /**
     * The cart line model.
     *
     * @var CartLine|null
     */
    protected ?CartLine $cartLine = null;

    /**
     * {@inheritDoc}
     */
    public function setShippingAddress(Addressable $address = null): self
    {
        $this->shippingAddress = $address;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrency(Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setBillingAddress(Addressable $address = null): self
    {
        $this->billingAddress = $address;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPurchasable(Purchasable $purchasable): self
    {
        $this->purchasable = $purchasable;

        return $this;
    }

    /**
     * Set the cart line.
     *
     * @param  CartLine  $cartLine
     * @return self
     */
    public function setCartLine(CartLine $cartLine): self
    {
        $this->cartLine = $cartLine;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getBreakdown($subTotal): TaxBreakdown
    {
        $taxZone = app(GetTaxZone::class)->execute($this->shippingAddress);
        $taxClass = $this->purchasable->getTaxClass();
        $taxAmounts = $taxZone->taxAmounts()->whereTaxClassId($taxClass->id)->get();

        $breakdown = new TaxBreakdown;

        foreach ($taxAmounts as $amount) {
            $result = round($subTotal * ($amount->percentage / 100));
            $amount = new TaxBreakdownAmount(
                price: new Price((int) $result, $this->currency, $this->purchasable->getUnitQuantity()),
                description: $amount->taxRate->name,
                identifier: "tax_rate_{$amount->taxRate->id}",
                percentage: $amount->percentage
            );
            $breakdown->addAmount($amount);
        }

        return $breakdown;
    }
}
