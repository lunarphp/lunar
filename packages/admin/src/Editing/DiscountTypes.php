<?php

namespace Lunar\Hub\Editing;

use Lunar\DiscountTypes\BuyXGetY;
use Lunar\DiscountTypes\AmountOff;
use Lunar\Hub\Http\Livewire\Components\Discounts\Types\BuyXGetY as TypesBuyXGetY;
use Lunar\Hub\Http\Livewire\Components\Discounts\Types\AmountOff as TypesAmountOff;

class DiscountTypes
{
    protected $mapping = [
        AmountOff::class => TypesAmountOff::class,
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
