<?php

namespace GetCandy\Hub\Http\Livewire\Components\Discounts\Types;

use GetCandy\Models\Currency;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class Coupon extends AbstractDiscountType
{
    /**
     * {@ineheritDoc}.
     */
    public function rules()
    {
        $rules = [
            'discount.data' => 'array',
            'discount.data.coupon' => 'required',
            'discount.data.percentage' => 'nullable|numeric',
            'discount.data.fixed_values' => 'array|min:0',
            'discount.data.fixed_value' => 'boolean',
        ];

        foreach ($this->currencies as $currency) {
            $rules["discount.data.fixed_values.{$currency->code}"] = 'nullable|numeric';
        }

        return $rules;
    }

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        if ($this->discount->id) {
            $this->discount = $this->discount->refresh();
        }

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
        return view('adminhub::livewire.components.discounts.types.coupon')
            ->layout('adminhub::layouts.base');
    }
}
