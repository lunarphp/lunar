<?php

namespace Lunar\Hub\Http\Livewire\Components\Discounts\Types;

use Livewire\Component;
use Lunar\Models\Discount;

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
        'discount.saved' => 'save',
        'product-search.selected' => 'selectProducts',
    ];

    public function save()
    {
        // ..
    }

    public function selectProducts($ids, $ref = null)
    {
        // ..
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
