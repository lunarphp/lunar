<?php

namespace Lunar\Panel\Http\Controllers\Customers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Customers\CreatesCustomer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Panel\Http\Requests\Customers\CustomerRequest;

class CustomerCreateController
{
    public function create(): Response
    {
        return Inertia::render('customers/Create', [
            'customerGroups' => CustomerGroup::all(['id', 'name']),
            'urls' => [
                'store' => route('panel.customers.store'),
                'index' => route('panel.customers.index'),
            ],
        ]);
    }

    public function store(CustomerRequest $request, CreatesCustomer $createsCustomer): RedirectResponse
    {
        $customer = $createsCustomer->execute(
            $request->customerAttributes(),
            $request->customerGroupIds(),
        );

        return redirect()
            ->route('panel.customers.edit', $customer)
            ->with('success', __('panel::customers.flash_created'));
    }
}
