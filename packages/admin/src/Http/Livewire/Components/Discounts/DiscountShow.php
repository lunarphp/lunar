<?php

namespace GetCandy\Hub\Http\Livewire\Components\Discounts;

use GetCandy\Facades\Discounts;
use GetCandy\Hub\Editing\DiscountTypes;
use GetCandy\Models\Discount;
use Livewire\Component;

class DiscountShow extends AbstractDiscount
{
    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    public Discount $discount;

    /**
     * {@inheritDoc}.
     */
    public function rules()
    {
        return array_merge([
            'discount.name' => 'required|unique:' . Discount::class . ',name,' . $this->discount->id,
            'discount.handle' => 'required|unique:' . Discount::class . ',handle,' . $this->discount->id,
            'discount.starts_at' => 'date',
            'discount.ends_at' => 'nullable|date|after:starts_at',
            'discount.type' => 'string|required',
        ], $this->getDiscountComponent()->rules());
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.discounts.show')
            ->layout('adminhub::layouts.app');
    }
}
