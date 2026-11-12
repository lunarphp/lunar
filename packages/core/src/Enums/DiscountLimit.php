<?php

namespace Lunar\Core\Enums;

/**
 * A usage limit on a discount or coupon, named by the column that holds it.
 */
enum DiscountLimit: string
{
    /** Uses across every customer. */
    case MaxUses = 'max_uses';

    /** Uses by one customer, identified by user or email. */
    case MaxUsesPerUser = 'max_uses_per_user';

    /** Uses of one coupon code, from `coupons.max_uses`. */
    case CouponMaxUses = 'coupon_max_uses';
}
