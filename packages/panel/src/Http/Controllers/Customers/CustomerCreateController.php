<?php

namespace Lunar\Panel\Http\Controllers\Customers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Customers\CreatesCustomer;
use Lunar\Core\Models\CustomerGroup;

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

    public function store(Request $request, CreatesCustomer $createsCustomer): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_identifier' => ['nullable', 'string', 'max:255'],
            'account_ref' => ['nullable', 'string', 'max:255'],
            'customer_group_ids' => ['nullable', 'array'],
            'customer_group_ids.*' => ['integer', Rule::exists((new CustomerGroup)->getTable(), 'id')],
        ]);

        $customer = $createsCustomer->execute(
            collect($validated)->except('customer_group_ids')->all(),
            $validated['customer_group_ids'] ?? [],
        );

        return redirect()
            ->route('panel.customers.edit', $customer)
            ->with('success', 'Customer created.');
    }
}
