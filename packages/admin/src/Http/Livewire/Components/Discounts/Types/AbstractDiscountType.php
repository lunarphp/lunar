<?php

namespace Lunar\Hub\Http\Livewire\Components\Discounts\Types;

use Lunar\Models\Discount;
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
