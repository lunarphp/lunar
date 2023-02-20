<?php

namespace Lunar\Tests\Stubs;

use Lunar\Base\Addressable;
use Lunar\Base\Purchasable;
use Lunar\Base\TaxDriver;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Base\ValueObjects\Cart\TaxBreakdownAmount;
use Lunar\DataTypes\Price;
use Lunar\Models\CartLine;
use Lunar\Models\Currency;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxRateAmount;

class TestTaxDriver implements TaxDriver
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
        $breakdown = new TaxBreakdown;

        $currency = Currency::first() ?:  Currency::factory()->create();

        $taxAmount = TaxRateAmount::factory()->create();

        $result = round($subTotal * ($taxAmount->percentage / 100));

        $variant = ProductVariant::factory()->create();

        $amount = new TaxBreakdownAmount(
            price: new Price((int) $result, $currency, $variant->getUnitQuantity()),
            description: $taxAmount->taxRate->name,
            identifier: "tax_rate_{$taxAmount->taxRate->id}",
            percentage: $taxAmount->percentage
        );

        $breakdown->addAmount($amount);

        return $breakdown;
    }
}
