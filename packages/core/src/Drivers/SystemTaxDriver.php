<?php

namespace Lunar\Core\Drivers;

use Lunar\Core\Contracts\Actions\Taxes\GetsTaxZone;
use Lunar\Core\Contracts\Addressable;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\TaxZone;
use Lunar\Core\Pricing\PriceCalculatorInterface;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;
use Lunar\Core\ValueObjects\Cart\TaxBreakdownAmount;
use Spatie\Blink\Blink;

class SystemTaxDriver implements TaxDriver
{
    public function __construct(
        protected GetsTaxZone $getsTaxZone,
        protected PriceCalculatorInterface $priceCalculator,
        protected Blink $blink,
    ) {}

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
     * An optional tax zone override supplied at the cart level.
     * When set this takes precedence over the address-derived zone.
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
        $taxZone = $this->taxZone ?? $this->getsTaxZone->execute($this->shippingAddress);
        $taxClass = $this->purchasable->getTaxClass();

        $taxAmounts = $this->blink->once('tax_zone_rates_'.$taxZone->id.'_'.$taxClass->id, function () use ($taxClass, $taxZone) {
            return $taxZone->taxAmounts()->with('taxRate')->whereTaxClassId($taxClass->id)->get();
        });

        if (prices_inc_tax()) {
            $totalTaxPercentage = (float) $taxAmounts->sum('percentage') / 100;
            $priceExTax = $this->priceCalculator->withoutTax((int) $subTotal, $totalTaxPercentage, $this->currency);

            if ($this->defaultTaxZone()->id === $taxZone->id) {
                $expectedTax = (int) $subTotal - $priceExTax;
                $weights = $taxAmounts
                    ->mapWithKeys(fn ($amount, $key) => [$key => (int) round((float) $amount->percentage * 10000)])
                    ->all();

                $allocations = $this->priceCalculator->distribute($expectedTax, $weights, $this->currency);

                $breakdown = new TaxBreakdown;

                foreach ($taxAmounts as $key => $amount) {
                    $breakdown->addAmount(new TaxBreakdownAmount(
                        price: new PriceValue($allocations[$key], $this->currency),
                        identifier: "tax_rate_{$amount->taxRate->id}",
                        description: $amount->taxRate->name,
                        percentage: $amount->percentage,
                    ));
                }

                return $breakdown;
            }

            $subTotal = $priceExTax;
        }

        $breakdown = new TaxBreakdown;

        foreach ($taxAmounts as $amount) {
            $result = $this->priceCalculator->percentage((int) $subTotal, (float) $amount->percentage / 100, $this->currency);

            $breakdown->addAmount(new TaxBreakdownAmount(
                price: new PriceValue($result, $this->currency),
                identifier: "tax_rate_{$amount->taxRate->id}",
                description: $amount->taxRate->name,
                percentage: $amount->percentage,
            ));
        }

        return $breakdown;
    }

    protected function defaultTaxZone()
    {
        return TaxZone::where('default', '=', 1)->first();
    }
}
