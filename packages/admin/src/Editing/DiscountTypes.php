<?php

namespace GetCandy\Hub\Editing;

use GetCandy\DiscountTypes\Coupon;

class DiscountTypes
{
    protected $mapping = [
        Coupon::class => 'hub.components.discounts.types.coupon',
    ];

    public function getComponent($type)
    {
        return $this->mapping[$type] ?? null;
    }
}
