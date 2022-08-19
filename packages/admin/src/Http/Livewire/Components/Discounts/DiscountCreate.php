<?php

namespace GetCandy\Hub\Http\Livewire\Components\Discounts;

use GetCandy\DiscountTypes\Coupon;
use GetCandy\Models\Discount;
use Illuminate\Support\Str;

class DiscountCreate extends AbstractDiscount
{
    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    public Discount $discount;

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->discount = new Discount([
            'type' => Coupon::class,
            'starts_at' => now()->startOfHour(),
            'data' => [],
        ]);
    }

    /**
     * {@inheritDoc}.
     */
    public function rules()
    {
        return array_merge([
            'discount.name' => 'required|unique:'.Discount::class.',name',
            'discount.handle' => 'required|unique:'.Discount::class.',handle',
            'discount.starts_at' => 'date',
            'discount.ends_at' => 'nullable|date|after:starts_at',
            'discount.type' => 'string|required',
        ], $this->getDiscountComponent()->rules());
    }

    /**
     * Handler for when the discount name is updated.
     *
     * @param  string  $val
     * @return void
     */
    public function updatedDiscountName($val)
    {
        if (! $this->discount->handle) {
            $this->discount->handle = Str::snake(strtolower($val));
        }
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.discounts.create')
            ->layout('adminhub::layouts.app');
    }
}
