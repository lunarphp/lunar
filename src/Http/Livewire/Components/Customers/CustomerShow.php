<?php

namespace GetCandy\Hub\Http\Livewire\Components\Customers;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\Customer;
use Livewire\Component;

class CustomerShow extends Component
{
    use Notifies;

    /**
     * The current customer in view.
     *
     * @var \GetCandy\Models\Customer
     */
    public Customer $customer;


    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [];
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.customers.show')
            ->layout('adminhub::layouts.base');
    }
}
