<?php

namespace Lunar\Tests\Core\Stubs;

use Lunar\Core\Contracts\Addressable;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Drivers\TaxDriver;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxRateAmount;
use Lunar\Core\Models\TaxZone;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;
use Lunar\Core\ValueObjects\Cart\TaxBreakdownAmount;

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
    protected Currency $currency;

    /**
     * The purchasable item.
     */
    protected Purchasable $purchasable;

    /**
     * The cart line.
     */
    protected CartLine $cartLine;

    /**
     * The optional tax zone override.
     */
    protected ?TaxZone $taxZone = null;

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
    public function setCurrency(Currency $currency): self
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
    public function setCartLine(CartLine $cartLine): self
    {
        $this->cartLine = $cartLine;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setTaxZone(?TaxZone $taxZone = null): self
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
                $taxClass->loadMissing('taxRateAmounts');
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
                price: new PriceValue((int) $result, $this->currency),
                identifier: "tax_rate_{$amount->taxRate->id}",
                description: $amount->taxRate->name,
                percentage: $amount->percentage
            );
            $breakdown->addAmount($amount);
        }

        return $breakdown;
    }
}
