<?php

namespace GetCandy\Hub\Http\Livewire\Components\Customers;

use Livewire\Component;

class CustomersIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.customers.index')
            ->layout('adminhub::layouts.base');
    }
}
