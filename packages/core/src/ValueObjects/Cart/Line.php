<?php

namespace Lunar\ValueObjects\Cart;

use Lunar\DataTypes\Price;
use Lunar\Models\CartLine;

final class Line
{
    /**
     * The cart line total.
     *
     * @var Price
     */
    public Price $total;

    /**
     * The cart line sub total.
     *
     * @var Price
     */
    public Price $subTotal;

    /**
     * The cart line tax amount.
     *
     * @var Price
     */
    public Price $taxAmount;

    /**
     * The cart line unit price.
     *
     * @var Price
     */
    public Price $unitPrice;

    /**
     * The discount total.
     *
     * @var Price
     */
    public Price $discountTotal;

    /**
     * All the tax breakdowns for the line.
     *
     * @var TaxBreakdown
     */
    public TaxBreakdown $taxBreakdown;

    /**
     * The cart line Eloquent database model.
     *
     * @var Price
     */
    public CartLine $model;
}
