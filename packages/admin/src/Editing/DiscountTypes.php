<?php

namespace Lunar\Hub\Editing;

use Lunar\DiscountTypes\BuyXGetY;
use Lunar\DiscountTypes\Discount;
use Lunar\Hub\Http\Livewire\Components\Discounts\Types\BuyXGetY as TypesBuyXGetY;
use Lunar\Hub\Http\Livewire\Components\Discounts\Types\Discount as TypesDiscount;

class DiscountTypes
{
    protected $mapping = [
        Discount::class => TypesDiscount::class,
        BuyXGetY::class => TypesBuyXGetY::class,
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
