<?php

namespace Lunar\Core\Enums;

/**
 * How a cart gets a discount.
 */
enum DiscountRedemptionMethod: string
{
    /** Applied to every eligible cart. */
    case Automatic = 'automatic';

    /** Applied only to a cart whose coupon code belongs to the discount. */
    case Coupon = 'coupon';
}
