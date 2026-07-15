<?php

namespace Lunar\Panel\Http\Controllers\Customers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class CustomerIndexController
{
    use ResolvesTableExtensions;

    /** @var string[] */
    protected array $sortable = ['first_name', 'last_name', 'company_name', 'created_at'];

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'full_name', 'label' => __('panel::customers.column_customer'), 'width' => 'minmax(0,1.4fr)'],
            ['key' => 'company_name', 'label' => __('panel::customers.column_company'), 'width' => 'minmax(0,1fr)'],
            ['key' => 'customer_groups', 'label' => __('panel::customers.column_groups'), 'width' => 'minmax(0,1fr)'],
            ['key' => 'created_at', 'label' => __('panel::customers.column_created'), 'width' => '120px', 'align' => 'right'],
        ];

        $sort = $request->string('sort')->value();
        $sort = in_array($sort, $this->sortable, true) ? $sort : 'created_at';

        $direction = $request->string('direction')->value() === 'asc' ? 'asc' : 'desc';

        $resolver = $this->resolveTable('customers.index');

        $customers = Customer::query()
            ->with('customerGroups:id,name')
            ->when($request->filled('q'), function ($query) use ($request, $resolver) {
                $term = $request->string('q')->value();
                $like = "%{$term}%";

                $query->where(function ($query) use ($like, $term, $resolver) {
                    $query->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('company_name', 'like', $like)
                        ->orWhere('tax_identifier', 'like', $like)
                        ->orWhere('account_ref', 'like', $like);

                    $resolver->applySearchQueries($query, $term);
                });
            })
            ->when($request->filled('customer_group_id'), fn ($query) => $query->whereHas(
                'customerGroups',
                fn ($query) => $query->where($query->getModel()->qualifyColumn('id'), $request->integer('customer_group_id')),
            ))
            ->when($request->string('type')->value() === 'business', fn ($query) => $query->whereNotNull('company_name')->where('company_name', '!=', ''))
            ->when($request->string('type')->value() === 'individual', fn ($query) => $query->where(fn ($query) => $query->whereNull('company_name')->orWhere('company_name', '')))
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(function (Customer $customer) use ($resolver) {
                $row = [
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
                    '_actions' => $resolver->resolveRowActionUrls($customer),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $customer->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('customers/Index', [
            'customers' => $customers,
            ...$this->tableProps($resolver, $this->columns),
            'customerGroups' => CustomerGroup::all(['id', 'name']),
            'totalCount' => Customer::count(),
            'filters' => $request->only(['q', 'customer_group_id', 'type', 'sort', 'direction']),
            'urls' => [
                'index' => route('panel.customers.index'),
                'create' => route('panel.customers.create'),
            ],
        ]);
    }
}
