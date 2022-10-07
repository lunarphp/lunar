<?php

namespace Lunar\Hub\Http\Livewire\Components\Discounts;

use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\Discount;

class DiscountsIndex extends Component
{
    use WithPagination;

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
    }

    public function getDiscountsProperty()
    {
        return Discount::paginate(25);
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.discounts.index')
            ->layout('adminhub::layouts.base');
    }
}
