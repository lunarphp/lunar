<?php

namespace Lunar\Panel\Http\Controllers\Customers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;

class CustomerIndexController
{
    /** @var string[] */
    protected array $sortable = ['first_name', 'last_name', 'company_name', 'created_at'];

    public function index(Request $request): Response
    {
        $sort = $request->string('sort')->value();
        $sort = in_array($sort, $this->sortable, true) ? $sort : 'created_at';

        $direction = $request->string('direction')->value() === 'asc' ? 'asc' : 'desc';

        $customers = Customer::query()
            ->with('customerGroups:id,name')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';

                $query->where(function ($query) use ($term) {
                    $query->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('company_name', 'like', $term)
                        ->orWhere('tax_identifier', 'like', $term)
                        ->orWhere('account_ref', 'like', $term);
                });
            })
            ->when($request->filled('customer_group_id'), fn ($query) => $query->whereHas(
                'customerGroups',
                fn ($query) => $query->where('id', $request->integer('customer_group_id')),
            ))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Customer $customer) => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'company_name' => $customer->company_name,
                'account_ref' => $customer->account_ref,
                'created_at' => $customer->created_at,
                'customer_groups' => $customer->customerGroups->map(fn (CustomerGroup $group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                ]),
                'edit_url' => route('panel.customers.edit', $customer),
            ]);

        return Inertia::render('customers/Index', [
            'customers' => $customers,
            'customerGroups' => CustomerGroup::all(['id', 'name']),
            'filters' => $request->only(['q', 'customer_group_id', 'sort', 'direction']),
            'urls' => [
                'index' => route('panel.customers.index'),
                'create' => route('panel.customers.create'),
            ],
        ]);
    }
}
