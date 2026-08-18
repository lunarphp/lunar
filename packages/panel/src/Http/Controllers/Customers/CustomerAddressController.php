<?php

namespace Lunar\Panel\Http\Controllers\Customers;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Customers\CreatesCustomerAddress;
use Lunar\Core\Contracts\Actions\Customers\DeletesCustomerAddress;
use Lunar\Core\Contracts\Actions\Customers\UpdatesCustomerAddress;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Customer;
use Lunar\Panel\Http\Requests\Customers\CustomerAddressRequest;

class CustomerAddressController
{
    public function store(CustomerAddressRequest $request, Customer $customer, CreatesCustomerAddress $createsCustomerAddress): RedirectResponse
    {
        $createsCustomerAddress->execute($customer, $request->validated());

        return back()->with('success', __('panel::customers.flash_address_added'));
    }

    public function update(CustomerAddressRequest $request, Customer $customer, Address $address, UpdatesCustomerAddress $updatesCustomerAddress): RedirectResponse
    {
        $updatesCustomerAddress->execute($address, $request->validated());

        return back()->with('success', __('panel::customers.flash_address_updated'));
    }

    public function destroy(Customer $customer, Address $address, DeletesCustomerAddress $deletesCustomerAddress): RedirectResponse
    {
        $deletesCustomerAddress->execute($address);

        return back()->with('success', __('panel::customers.flash_address_deleted'));
    }
}
