<?php

namespace Lunar\Shipping\Panel\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Panel\Support\DriverOptions;

class MethodIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function __construct(protected DriverOptions $driverOptions) {}

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('shipping::methods.column_name'), 'width' => 'minmax(0, 1.4fr)'],
            ['key' => 'code', 'label' => __('shipping::methods.column_code'), 'width' => '140px'],
            ['key' => 'driver', 'label' => __('shipping::methods.column_driver'), 'width' => '160px'],
            ['key' => 'availability', 'label' => __('shipping::methods.column_availability'), 'width' => '160px'],
        ];

        $resolver = $this->resolveTable('shipping.methods.index');
        $driverLabels = collect($this->driverOptions->all())->pluck('label', 'key');
        $groupCount = CustomerGroup::query()->count();

        $methods = ShippingMethod::query()
            ->withCount(['customerGroups as enabled_groups_count' => fn ($query) => $query->where('enabled', true)])
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString()
            ->through(function (ShippingMethod $method) use ($resolver, $driverLabels, $groupCount): array {
                $row = [
                    'id' => $method->id,
                    'name' => $method->name,
                    'code' => $method->code,
                    'driver' => $method->driver,
                    'driver_label' => $driverLabels->get($method->driver, $method->driver),
                    'enabled_groups_count' => (int) $method->getAttribute('enabled_groups_count'),
                    'groups_count' => $groupCount,
                    'urls' => [
                        'edit' => route('panel.settings.shipping.methods.edit', $method),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($method),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $method->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('shipping::settings/shipping/methods/Index', [
            'methods' => $methods,
            'drivers' => $this->driverOptions->all(),
            ...$this->tableProps($resolver, $this->columns, $request),
            'urls' => [
                'index' => route('panel.settings.shipping.methods.index'),
                'store' => route('panel.settings.shipping.methods.store'),
            ],
        ]);
    }
}
