<?php

namespace GetCandy\Discounts\Http\Livewire\Components;

use GetCandy\Discounts\Models\Discount;
use GetCandy\Discounts\Models\DiscountCondition;
use Livewire\Component;

class CouponEdit extends Component
{
    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    public DiscountCondition $condition;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('discounts::discounts.components.coupon-edit')
            ->layout('adminhub::layouts.base');
    }
}
