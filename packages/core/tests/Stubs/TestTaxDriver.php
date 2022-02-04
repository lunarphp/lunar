<?php

namespace GetCandy\Tests\Stubs;

use GetCandy\Actions\Taxes\GetTaxZone;
use GetCandy\Base\Addressable;
use GetCandy\Base\Purchasable;
use GetCandy\Base\TaxDriver;
use GetCandy\DataTypes\Price;
use GetCandy\Models\Currency;
use GetCandy\Models\TaxRateAmount;

class TestTaxDriver implements TaxDriver
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
    public function setShippingAddress(Addressable $address = null)
    {
        $this->shippingAddress = $address;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setBillingAddress(Addressable $address = null)
    {
        $this->billingAddress = $address;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPurchasable(Purchasable $purchasable)
    {
        $this->purchasable = $purchasable;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getBreakdown($subTotal)
    {
        return collect([
           TaxRateAmount::factory()->create()
        ]);
    }
}
