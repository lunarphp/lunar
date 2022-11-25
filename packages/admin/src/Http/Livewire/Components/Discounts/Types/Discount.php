<?php

namespace Lunar\Hub\Http\Livewire\Components\Discounts\Types;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Lunar\Models\Currency;

class Discount extends AbstractDiscountType
{
    /**
     * {@ineheritDoc}.
     */
    public function rules()
    {
        $rules = [
            'discount.data' => 'array',
            'discount.data.percentage' => 'required_if:discount.data.fixed_value,false|nullable|numeric|min:1',
            'discount.data.fixed_values' => 'array|min:0',
            'discount.data.fixed_value' => 'boolean',
        ];

        foreach ($this->currencies as $currency) {
            $rules["discount.data.fixed_values.{$currency->code}"] = 'required_if:discount.data.fixed_value,true|nullable|numeric|min:1';
        }

        return $rules;
    }

    public function getValidationMessages()
    {
        $messages = [
            'discount.data.percentage.required_if' => 'This field is required',
            'discount.data.percentage.min' => 'Percentage must be at least :min',
            'discount.data.max_reward_qty.required' => 'This field is required',
        ];

        foreach ($this->currencies as $currency) {
            $messages["discount.data.fixed_values.{$currency->code}.required_if"] = 'This field is required';
        }

        return $messages;
    }

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        parent::mount();

        if (empty($this->discount->data)) {
            $this->discount->data = [
                'coupon' => null,
                'fixed_value' => false,
            ];
        }
    }

    /**
     * Listen to when the coupon is updated and emit the data change.
     *
     * @param  string  $val
     * @return void
     */
    public function updatedDiscountDataCoupon($val)
    {
        $data = (array) $this->discount->data;

        $data['coupon'] = strtoupper(
            Str::snake(
                strtolower($val)
            )
        );

        $this->discount->data = $data;
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
        return view('adminhub::livewire.components.discounts.types.discount')
            ->layout('adminhub::layouts.base');
    }
}
