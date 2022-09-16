<?php

namespace Lunar\Hub\Editing;

use Lunar\DiscountTypes\Coupon;
use Lunar\DiscountTypes\ProductDiscount;
use Lunar\Hub\Http\Livewire\Components\Discounts\Types\Coupon as TypesCoupon;
use Lunar\Hub\Http\Livewire\Components\Discounts\Types\ProductDiscount as TypesProductDiscount;

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
