<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Discounts;

use GetCandy\Models\Discount;
use Livewire\Component;

class DiscountCreate extends Component
{
    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    public Discount $discount;

    public function mount()
    {
        $this->discount = new Discount;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.discounts.create')
            ->layout('adminhub::layouts.app', [
                'title' => __('adminhub::components.discounts.create.title'),
            ]);
    }
}
