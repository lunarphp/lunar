<?php

namespace GetCandy\Hub\Http\Livewire\Components\Customers;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\Customer;
use GetCandy\Models\CustomerGroup;
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
     * An array of synced customer groups.
     *
     * @var array
     */
    public array $syncedGroups = [];

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            'syncedGroups' => 'array',
            'customer.title' => 'string',
            'customer.first_name' => 'string',
            'customer.last_name' => 'string',
            'customer.company_name' => 'nullable|string',
            'customer.vat_no' => 'nullable|string',
        ];
    }

    /**
     * Called when the component is mounted.
     *
     * @return void
     */
    public function mount()
    {
        $this->syncedGroups = $this->customer->customerGroups->pluck('id')->map(fn($id) => (string) $id)->toArray();
    }

    /**
     * Save the customer record.
     *
     * @return void
     */
    public function save()
    {
        $this->validate();

        $this->customer->customerGroups()->sync(
            $this->syncedGroups
        );

        $this->customer->save();

        $this->notify(
            __('adminhub::notifications.customer.updated')
        );
    }

    /**
     * Return the computed customer groups.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCustomerGroupsProperty()
    {
        return CustomerGroup::get();
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
