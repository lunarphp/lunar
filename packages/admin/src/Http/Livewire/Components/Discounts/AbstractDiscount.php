<?php

namespace GetCandy\Hub\Http\Livewire\Components\Discounts;

use GetCandy\Facades\Discounts;
use GetCandy\Hub\Editing\DiscountTypes;
use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
use GetCandy\Models\Discount;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;

abstract class AbstractDiscount extends Component
{
    use WithLanguages;

    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    public Discount $discount;

    /**
     * {@inheritDoc}
     */
    public function dehydrate()
    {
        $this->emit('parentComponentErrorBag', $this->getErrorBag());
    }

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'discountData.updated' => 'syncDiscountData',
    ];

    /**
     * Get the collection attribute data.
     *
     * @return void
     */
    public function getAttributeDataProperty()
    {
        return $this->discount->attribute_data;
    }

    /**
     * Return the available discount types.
     *
     * @return array
     */
    public function getDiscountTypesProperty()
    {
        return Discounts::getTypes();
    }

    /**
     * Return the component for the selected discount type.
     *
     * @return Component
     */
    public function getDiscountComponent()
    {
        return (new DiscountTypes)->getComponent($this->discount->type);
    }

    /**
     * Sync the discount data with what's provided.
     *
     * @param  array  $data
     * @return void
     */
    public function syncDiscountData(array $data)
    {
        $this->discount->data = $data;
    }

    /**
     * Save the discount.
     *
     * @return RedirectResponse
     */
    public function save()
    {
        $this->validate();
        $this->discount->save();

        return redirect()->route('hub.discounts.show', $this->discount->id);
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
