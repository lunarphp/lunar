<?php

namespace Lunar\ValueObjects\Cart;

use Lunar\DataTypes\Price;

final class Shipping
{
    /**
     * The shipping sub total.
     *
     * @var Price
     */
    public Price $shippingSubTotal;

    /**
     * The shipping tax total.
     *
     * @var Price
     */
    public Price $shippingTaxTotal;

    /**
     * The shipping total.
     *
     * @var Price
     */
    public Price $shippingTotal;

    /**
     * The tax breakdown.
     *
     * @var TaxBreakdown
     */
    public TaxBreakdown $taxBreakdown;

    /**
     * The applied shipping option.
     *
     * @var ShippingOption
     */
    public ?ShippingOption $shippingOption;
}
