<?php

namespace GetCandy\Hub\Http\Livewire\Components\Discounts;

use GetCandy\Facades\Discounts;
use GetCandy\Hub\Editing\DiscountTypes;
use GetCandy\Models\Discount;
use Livewire\Component;

class DiscountShow extends Component
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
            'discount.name' => 'required',
            'discount.starts_at' => 'datetime',
            'discount.ends_at' => 'nullable|datetime|after:starts_at',
            'discount.type' => 'string|required',
            // 'discount.handle' => 'required|un'
        ];
    }

    protected $listeners = [
        'discountData.updated' => 'syncDiscountData',
    ];

    public function getDiscountTypesProperty()
    {
        return Discounts::getTypes();
    }

    public function getUiProperty()
    {
        return (new DiscountTypes)->getComponent($this->discount->type);
    }

    public function syncDiscountData(array $data)
    {
        $this->discount->data = $data;
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
