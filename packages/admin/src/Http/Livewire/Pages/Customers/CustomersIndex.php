<?php

namespace Lunar\Hub\Http\Livewire\Pages\Customers;

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
        return view('adminhub::livewire.pages.customers.index')
            ->layout('adminhub::layouts.app', [
                'title' => 'Customers',
            ]);
    }
}
