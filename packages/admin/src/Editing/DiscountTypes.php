<?php

namespace Lunar\Hub\Editing;

use Livewire\Component;
use Lunar\DiscountTypes\AmountOff;
use Lunar\DiscountTypes\BuyXGetY;
use Lunar\Hub\Base\DiscountTypesInterface;
use Lunar\Hub\Http\Livewire\Components\Discounts\Types\AmountOff as TypesAmountOff;
use Lunar\Hub\Http\Livewire\Components\Discounts\Types\BuyXGetY as TypesBuyXGetY;

class DiscountTypes implements DiscountTypesInterface
{
    protected $mapping = [
        AmountOff::class => TypesAmountOff::class,
        BuyXGetY::class => TypesBuyXGetY::class,
    ];

    public function register($discountType, $component): self
    {
        $this->mapping[$discountType] = $component;

        return $this;
    }

    public function getComponent($type): ?Component
    {
        $component = $this->mapping[$type] ?? null;

        if (! $component) {
            return null;
        }

        return app($component);
    }
}
