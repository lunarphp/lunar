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

        $taxAmounts = $taxZone->taxAmounts()->whereTaxClassId($taxClass->id)->get();

        if (prices_inc_tax()) {
            // Remove tax from price
            $totalTaxPercentage = $taxAmounts->sum('percentage') / 100; // E.g. 0.2 for 20%
            $priceExTax = round($subTotal / (1 + $totalTaxPercentage));

            // Check to see if the included tax uses the same tax zone
            if ($this->defaultTaxZone() === $taxZone) {
                // Manually return the tax breakdown
                $breakdown = new TaxBreakdown;

                $taxTally = 0;

                foreach ($taxAmounts as $key => $amount) {
                    $taxTally += $result;

                    if ($taxAmounts->keys()->last() == $key) {
                        // Ensure the final tax amount adds up to the original price
                        $result = $subTotal - $taxTally;
                    } else {
                        $result = round($priceExTax * ($amount->percentage / 100));
                    }

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

            // Set subTotal to ex. tax price
            $subTotal = $priceExTax;
        }

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
