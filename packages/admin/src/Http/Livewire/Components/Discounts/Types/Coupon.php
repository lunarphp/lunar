<?php

namespace GetCandy\Hub\Http\Livewire\Components\Discounts\Types;

use GetCandy\Models\Currency;
use GetCandy\Models\Discount;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Coupon extends Component
{
    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    public Discount $discount;

    /**
     * {@ineheritDoc}.
     */
    public function rules()
    {
        return [
            'discount.data' => 'array',
            'discount.data.coupon' => 'required',
            'discount.data.value' => 'required|numeric',
        ];
    }

    /**
     * Handle when the discount data is updated.
     *
     * @return void
     */
    public function updatedDiscount()
    {
        $this->emitUp('discountData.updated', $this->discount->data);
    }

    /**
     * Return the available currencies.
     *
     * @return Collection
     */
    public function getCurrenciesProperty()
    {
        return Currency::get();
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.discounts.types.coupon')
            ->layout('adminhub::layouts.base');
    }
}
