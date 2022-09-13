<?php

namespace Lunar\Hub\Http\Livewire\Pages\Customers;

use Livewire\Component;
use Lunar\Models\Customer;

class CustomerShow extends Component
{
    /**
     * The Product we are currently editing.
     *
     * @var \Lunar\Models\Product
     */
    public Customer $customer;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.customers.show')
            ->layout('adminhub::layouts.app', [
                'title' => $this->customer->fullName,
            ]);
    }
}
