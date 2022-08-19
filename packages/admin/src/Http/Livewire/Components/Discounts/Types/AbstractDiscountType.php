<?php

namespace GetCandy\Hub\Http\Livewire\Components\Discounts\Types;

use GetCandy\Models\Discount;
use Livewire\Component;

abstract class AbstractDiscountType extends Component
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
    protected $listeners = [
        'parentComponentErrorBag',
    ];

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
                'fixed_value' => true
            ];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function parentComponentErrorBag($errorBag)
    {
        $this->setErrorBag($errorBag);
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
}
