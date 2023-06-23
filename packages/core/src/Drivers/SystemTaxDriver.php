<?php

namespace Lunar\Drivers;

use Lunar\Actions\Taxes\GetTaxZone;
use Lunar\Base\Addressable;
use Lunar\Base\Purchasable;
use Lunar\Base\TaxDriver;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Base\ValueObjects\Cart\TaxBreakdownAmount;
use Lunar\DataTypes\Price;
use Lunar\Models\CartLine;
use Lunar\Models\Currency;
use Spatie\LaravelBlink\BlinkFacade as Blink;

class SystemTaxDriver implements TaxDriver
{
    /**
     * The taxable shipping address.
     */
    protected ?Addressable $shippingAddress = null;

    /**
     * The taxable billing address.
     */
    protected ?Addressable $billingAddress = null;

    /**
     * The currency model.
     */
    protected Currency $currency;

    /**
     * The purchasable item.
     */
    protected Purchasable $purchasable;

    /**
     * The cart line model.
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
        $taxAmounts = Blink::once('tax_zone_rates_'.$taxZone->id.'_'.$taxClass->id, function () use ($taxClass, $taxZone){
            return $taxZone->taxAmounts->first(
               fn($amount) => $amount->tax_class_id == $taxClass->id
            )->get();
        });

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
