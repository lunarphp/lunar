<?php

namespace GetCandy\Hub\Http\Livewire\Components\Discounts\Types;

use GetCandy\Models\Currency;
use GetCandy\Models\Discount;
use Illuminate\Support\Collection;
use Livewire\Component;

class ProductDiscount extends Component
{
    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    public Discount $discount;

    public Collection $conditions;

    public Collection $rewards;

    /**
     * {@ineheritDoc}.
     */
    public function rules()
    {
        return [
            'discount.data' => 'array',
            'discount.data.min_qty' => 'required',
            'discount.data.reward_qty' => 'required|numeric',
            'conditions' => 'array',
        ];
    }

    public function mount()
    {
        $this->conditions = $this->purchasableConditions->pluck('id')->unique()->values();

        $this->rewards = $this->purchasableRewards->pluck('id')->unique()->values();
    }

    public function getPurchasableConditionsProperty()
    {
        return $this->discount->purchasableConditions->pluck('purchasable.product')->unique('id');
    }

    public function getPurchasableRewardsProperty()
    {
        return $this->discount->purchasableRewards->pluck('purchasable.product')->unique('id');
    }

    /**
     * Handle when the discount data is updated.
     *
     * @return void
     */
    public function updatedDiscount()
    {
        $this->emitUp('discountData.updated', $this->discount->data);
        $this->emitUp('discount.conditions', $this->conditions->toArray());
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
        return view('adminhub::livewire.components.discounts.types.product-discount')
            ->layout('adminhub::layouts.base');
    }
}
