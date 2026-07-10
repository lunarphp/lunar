<?php

namespace Lunar\Panel\Http\Controllers\Customers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lunar\Core\Contracts\Actions\Customers\CreatesCustomerAddress;
use Lunar\Core\Contracts\Actions\Customers\DeletesCustomerAddress;
use Lunar\Core\Contracts\Actions\Customers\UpdatesCustomerAddress;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Customer;

class CustomerAddressController
{
    public function store(Request $request, Customer $customer, CreatesCustomerAddress $createsCustomerAddress): RedirectResponse
    {
        $createsCustomerAddress->execute($customer, $this->validated($request));

        return back()->with('success', 'Address added.');
    }

    public function update(Request $request, Customer $customer, Address $address, UpdatesCustomerAddress $updatesCustomerAddress): RedirectResponse
    {
        $updatesCustomerAddress->execute($address, $this->validated($request));

        return back()->with('success', 'Address updated.');
    }

    public function destroy(Customer $customer, Address $address, DeletesCustomerAddress $deletesCustomerAddress): RedirectResponse
    {
        $deletesCustomerAddress->execute($address);

        return back()->with('success', 'Address deleted.');
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_identifier' => ['nullable', 'string', 'max:255'],
            'line_one' => ['required', 'string', 'max:255'],
            'line_two' => ['nullable', 'string', 'max:255'],
            'line_three' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:255'],
            'country_id' => ['required', 'integer', 'exists:'.(new Country)->getTable().',id'],
            'delivery_instructions' => ['nullable', 'string'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'shipping_default' => ['nullable', 'boolean'],
            'billing_default' => ['nullable', 'boolean'],
        ]);
    }
}
