<?php

namespace GetCandy\Hub\Editing;

use GetCandy\DiscountTypes\Coupon;
use GetCandy\DiscountTypes\ProductDiscount;

class DiscountTypes
{
    protected $mapping = [
        Coupon::class => 'hub.components.discounts.types.coupon',
        ProductDiscount::class => 'hub.components.discounts.types.product-discount',
    ];

    public function getComponent($type)
    {
        return $this->mapping[$type] ?? null;
    }
}
