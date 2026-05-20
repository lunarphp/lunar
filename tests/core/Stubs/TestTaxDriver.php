<?php

namespace Lunar\Tests\Core\Stubs;

use Lunar\Base\Addressable;
use Lunar\Base\Purchasable;
use Lunar\Base\TaxDriver;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Base\ValueObjects\Cart\TaxBreakdownAmount;
use Lunar\DataTypes\Price;
use Lunar\Models\Contracts\CartLine as CartLineContract;
use Lunar\Models\Contracts\Currency as CurrencyContract;
use Lunar\Models\Contracts\TaxZone as TaxZoneContract;
use Lunar\Models\Currency;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxRateAmount;

class TestTaxDriver implements TaxDriver
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
    protected CurrencyContract $currency;

    /**
     * The purchasable item.
     */
    protected Purchasable $purchasable;

    /**
     * The cart line.
     */
    protected CartLineContract $cartLine;

    /**
     * The optional tax zone override.
     */
    protected ?TaxZoneContract $taxZone = null;

    /**
     * {@inheritDoc}
     */
    public function setShippingAddress(?Addressable $address = null): self
    {
        $this->shippingAddress = $address;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrency(CurrencyContract $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setBillingAddress(?Addressable $address = null): self
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
    public function setCartLine(CartLineContract $cartLine): self
    {
        $this->cartLine = $cartLine;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setTaxZone(?TaxZoneContract $taxZone = null): self
    {
        $this->taxZone = $taxZone;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getBreakdown($subTotal): TaxBreakdown
    {
        $breakdown = new TaxBreakdown;

        if ($this->purchasable) {
            $taxClass = $this->purchasable->getTaxClass();

            // When a zone override is provided, restrict to that zone's rate amounts
            // (mirrors SystemTaxDriver behaviour so cart-level zone tests work correctly).
            if ($this->taxZone) {
                $taxAmounts = $this->taxZone->taxAmounts()->whereTaxClassId($taxClass->id)->get();
            } else {
                $taxAmounts = $taxClass->taxRateAmounts;
            }
        } else {
            $taxAmounts = TaxRateAmount::factory(2)->create();
        }

        $currency = Currency::first() ?: Currency::factory()->create();

        $variant = $this->purchasable ?: ProductVariant::factory()->create();

        if (prices_inc_tax()) {
            // Remove tax from price
            $totalTaxPercentage = $taxAmounts->sum('percentage') / 100; // E.g. 0.2 for 20%
            $subTotal = round($subTotal / (1 + $totalTaxPercentage));
        }

        foreach ($taxAmounts as $amount) {
            $result = round($subTotal * ($amount->percentage / 100));

            $amount = new TaxBreakdownAmount(
                price: new Price((int) $result, $this->currency, $this->purchasable->getUnitQuantity()),
                identifier: "tax_rate_{$amount->taxRate->id}",
                description: $amount->taxRate->name,
                percentage: $amount->percentage
            );
            $breakdown->addAmount($amount);
        }

        return $breakdown;
    }
}
