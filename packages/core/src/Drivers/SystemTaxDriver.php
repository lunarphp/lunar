<?php

namespace GetCandy\Drivers;

use GetCandy\Actions\Taxes\GetTaxZone;
use GetCandy\Base\Addressable;
use GetCandy\Base\DataTransferObjects\TaxBreakdownAmount;
use GetCandy\Base\Purchasable;
use GetCandy\Base\TaxDriver;
use GetCandy\DataTypes\Price;
use GetCandy\Models\CartLine;
use GetCandy\Models\Currency;
use Illuminate\Support\Collection;

class SystemTaxDriver implements TaxDriver
{
    /**
     * The taxable shipping address.
     *
     * @var \GetCandy\Base\Addressable|null
     */
    protected ?Addressable $shippingAddress = null;

    /**
     * The taxable billing address.
     *
     * @var \GetCandy\Base\Addressable|null
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
     * @param CartLine $cartLine
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
    public function getBreakdown($subTotal): Collection
    {
        $taxZone = app(GetTaxZone::class)->execute($this->shippingAddress);
        $taxClass = $this->purchasable->getTaxClass();
        $taxAmounts = $taxZone->taxAmounts()->whereTaxClassId($taxClass->id)->get();

        $amounts = collect();

        foreach ($taxAmounts as $amount) {
            $result = round($subTotal * ($amount->percentage / 100));
            $amount = new TaxBreakdownAmount(
                price: new Price((int) $result, $this->currency, $this->purchasable->getUnitQuantity()),
                description: $amount->taxRate->name,
                identifier: "tax_rate_{$amount->taxRate->id}"
            );
            dd($amount);
            $amount->total = new Price((int) $result, $this->currency, $this->purchasable->getUnitQuantity());
            $amounts->push($amount);
        }

        return $amounts;
    }
}
