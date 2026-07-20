<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class CustomerGroupIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('panel::customer_groups.column_name'), 'width' => 'minmax(0, 1.4fr)'],
            ['key' => 'handle', 'label' => __('panel::customer_groups.column_handle'), 'width' => 'minmax(0, 1fr)'],
            ['key' => 'customers_count', 'label' => __('panel::customer_groups.column_customers'), 'width' => '110px', 'align' => 'right'],
        ];

        $resolver = $this->resolveTable('customer-groups.index');

        $customerGroups = CustomerGroup::query()
            ->withCount('customers')
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString()
            ->through(function (CustomerGroup $customerGroup) use ($resolver): array {
                $row = [
                    'id' => $customerGroup->id,
                    'name' => $customerGroup->name,
                    'handle' => $customerGroup->handle,
                    'default' => $customerGroup->default,
                    'customers_count' => (int) $customerGroup->getAttribute('customers_count'),
                    'urls' => [
                        'edit' => route('panel.settings.customer-groups.edit', $customerGroup),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($customerGroup),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $customerGroup->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('settings/customer-groups/Index', [
            'customerGroups' => $customerGroups,
            ...$this->tableProps($resolver, $this->columns, $request),
            'urls' => [
                'index' => route('panel.settings.customer-groups.index'),
                'store' => route('panel.settings.customer-groups.store'),
            ],
        ]);
    }
}
