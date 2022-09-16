<?php

namespace Lunar\Hub\Http\Livewire\Pages\Discounts;

use Livewire\Component;

class DiscountsIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.discounts.index')
            ->layout('adminhub::layouts.app', [
                'title' => 'Discounts',
            ]);
    }
}
