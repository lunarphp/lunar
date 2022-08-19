<?php

namespace GetCandy\Hub\Editing;

use GetCandy\DiscountTypes\Coupon;
use GetCandy\DiscountTypes\ProductDiscount;
use GetCandy\Hub\Http\Livewire\Components\Discounts\Types\Coupon as TypesCoupon;
use GetCandy\Hub\Http\Livewire\Components\Discounts\Types\ProductDiscount as TypesProductDiscount;

class DiscountTypes
{
    protected $mapping = [
        Coupon::class => TypesCoupon::class,
        ProductDiscount::class => TypesProductDiscount::class,
    ];

    public function getComponent($type)
    {
        $component = $this->mapping[$type] ?? null;

        if (! $component) {
            return null;
        }

        return app($component);
    }
}
