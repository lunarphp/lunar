<?php

namespace Lunar\Hub\Http\Livewire\Pages\Discounts;

use Lunar\Models\Discount;
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
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.discounts.show')
            ->layout('adminhub::layouts.app', [
                'title' => 'Discounts',
            ]);
    }
}
