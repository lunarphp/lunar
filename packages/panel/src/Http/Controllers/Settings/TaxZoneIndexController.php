<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\TaxZone;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class TaxZoneIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('panel::tax_zones.column_name'), 'width' => 'minmax(0, 1.4fr)'],
            ['key' => 'zone_type', 'label' => __('panel::tax_zones.column_type'), 'width' => '120px'],
            ['key' => 'rates_count', 'label' => __('panel::tax_zones.column_rates'), 'width' => '90px', 'align' => 'right'],
            ['key' => 'status', 'label' => __('panel::tax_zones.column_status'), 'width' => '110px'],
        ];

        $resolver = $this->resolveTable('tax-zones.index');

        $taxZones = TaxZone::query()
            ->withCount('taxRates')
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString()
            ->through(function (TaxZone $taxZone) use ($resolver): array {
                $row = [
                    'id' => $taxZone->id,
                    'name' => $taxZone->name,
                    'zone_type' => $taxZone->zone_type,
                    'active' => $taxZone->active,
                    'default' => $taxZone->default,
                    'rates_count' => (int) $taxZone->getAttribute('tax_rates_count'),
                    'urls' => [
                        'edit' => route('panel.settings.tax-zones.edit', $taxZone),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($taxZone),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $taxZone->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('settings/tax-zones/Index', [
            'taxZones' => $taxZones,
            ...$this->tableProps($resolver, $this->columns, $request),
            'urls' => [
                'index' => route('panel.settings.tax-zones.index'),
                'store' => route('panel.settings.tax-zones.store'),
            ],
        ]);
    }
}
