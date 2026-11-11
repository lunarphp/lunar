<?php

namespace Lunar\Panel\Http\Controllers\Customers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Customers\CreatesCustomer;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Panel\Forms\FormSlices;
use Lunar\Panel\Http\Requests\Customers\CustomerRequest;

class CustomerCreateController
{
    public function create(FormSlices $formSlices): Response
    {
        return Inertia::render('customers/Create', [
            'customerGroups' => CustomerGroup::all(['id', 'name']),
            'formSliceValues' => $formSlices->values(new Customer) ?: (object) [],
            'urls' => [
                'store' => route('panel.customers.store'),
                'index' => route('panel.customers.index'),
            ],
        ]);
    }

    public function store(CustomerRequest $request, CreatesCustomer $createsCustomer, FormSlices $formSlices): RedirectResponse
    {
        $customer = $formSlices->save($request->sliceInput(), fn () => $createsCustomer->execute(
            $request->customerAttributes(),
            $request->customerGroupIds(),
        ));

        return redirect()
            ->route('panel.customers.edit', $customer)
            ->with('success', __('panel::customers.flash_created'));
    }
}
