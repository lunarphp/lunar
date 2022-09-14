<?php

namespace Lunar\Hub\Http\Livewire\Components\Orders;

use Livewire\Component;

class OrdersIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.orders.index')
            ->layout('adminhub::layouts.base');
    }
}
