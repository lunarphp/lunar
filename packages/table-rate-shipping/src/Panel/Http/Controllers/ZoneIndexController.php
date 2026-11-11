<?php

namespace Lunar\Shipping\Panel\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;
use Lunar\Shipping\Models\ShippingZone;

class ZoneIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('shipping::zones.column_name'), 'width' => 'minmax(0, 1.4fr)'],
            ['key' => 'type', 'label' => __('shipping::zones.column_type'), 'width' => '200px'],
            ['key' => 'rates_count', 'label' => __('shipping::zones.column_rates'), 'width' => '90px', 'align' => 'right'],
            ['key' => 'exclusion_lists_count', 'label' => __('shipping::zones.column_exclusion_lists'), 'width' => '120px', 'align' => 'right'],
        ];

        $resolver = $this->resolveTable('shipping.zones.index');

        $zones = ShippingZone::query()
            ->withCount(['rates', 'shippingExclusions'])
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString()
            ->through(function (ShippingZone $zone) use ($resolver): array {
                $row = [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'type' => $zone->type,
                    'rates_count' => (int) $zone->getAttribute('rates_count'),
                    'exclusion_lists_count' => (int) $zone->getAttribute('shipping_exclusions_count'),
                    'urls' => [
                        'edit' => route('panel.settings.shipping.zones.edit', $zone),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($zone),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $zone->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('shipping::settings/shipping/zones/Index', [
            'zones' => $zones,
            ...$this->tableProps($resolver, $this->columns, $request),
            'urls' => [
                'index' => route('panel.settings.shipping.zones.index'),
                'store' => route('panel.settings.shipping.zones.store'),
            ],
        ]);
    }
}
