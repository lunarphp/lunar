<?php

namespace Lunar\ValueObjects\Cart;

use Lunar\DataTypes\Price;

final class CartTotals
{
    /**
     * The cart sub total.
     *
     * @var Price
     */
    public Price $subTotal;

    /**
     * The shipping total for the cart.
     *
     * @var Price
     */
    public Price $shippingTotal;

    /**
     * The discount total.
     *
     * @var Price
     */
    public Price $discountTotal;

    /**
     * The cart tax total.
     *
     * @var Price
     */
    public Price $taxTotal;

    /**
     * The cart total.
     *
     * @var Price
     */
    public Price $total;

    // Lines
    // Shipping
    // Discounts
    // TaxBreakdown(s)
}
