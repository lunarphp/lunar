<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Orders;

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
        return view('adminhub::livewire.pages.orders.index')
            ->layout('adminhub::layouts.app', [
                'title' => 'Orders',
            ]);
    }
}
