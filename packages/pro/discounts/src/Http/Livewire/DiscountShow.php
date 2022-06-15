<?php

namespace GetCandy\Discounts\Http\Livewire;

use GetCandy\Discounts\Models\Discount;
use GetCandy\Hub\Http\Livewire\Traits\WithAttributes;
use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
use GetCandy\Models\Attribute;
use Livewire\Component;

class DiscountShow extends Component
{
    use WithAttributes,
        WithLanguages;

    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    public Discount $discount;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('discounts::discounts.show')
            ->layout('adminhub::layouts.app');
    }

    /**
     * Get the discount attribute data.
     *
     * @return array
     */
    public function getAttributeDataProperty()
    {
        return $this->discount->attribute_data;
    }

    public function getConditionsProperty()
    {
        return $this->discount->conditions;
    }

    /**
     * Returns all available attributes.
     *
     * @return void
     */
    public function getAvailableAttributesProperty()
    {
        return Attribute::whereAttributeType(Discount::class)->orderBy('position')->get();
    }
}
