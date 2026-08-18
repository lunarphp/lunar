<?php

namespace Lunar\Panel\Http\Controllers\Customers;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Customers\UpdatesCustomer;
use Lunar\Core\Models\Customer;
use Lunar\Panel\Http\Requests\Customers\CustomerNotesRequest;

class CustomerNotesController
{
    public function update(CustomerNotesRequest $request, Customer $customer, UpdatesCustomer $updatesCustomer): RedirectResponse
    {
        $updatesCustomer->execute($customer, $request->validated());

        return back()->with('success', __('panel::customers.flash_notes_updated'));
    }
}
