<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\StockLevel;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class LocationIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('panel::locations.column_name'), 'width' => 'minmax(0, 1.4fr)'],
            ['key' => 'handle', 'label' => __('panel::locations.column_handle'), 'width' => 'minmax(0, 1fr)'],
            ['key' => 'stocked_variants_count', 'label' => __('panel::locations.column_stocked'), 'width' => '130px', 'align' => 'right'],
        ];

        $resolver = $this->resolveTable('locations.index');

        $stocked = StockLevel::query()
            ->selectRaw('location_id, COUNT(*) AS aggregate')
            ->groupBy('location_id')
            ->pluck('aggregate', 'location_id');

        $locations = Location::query()
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString()
            ->through(function (Location $location) use ($resolver, $stocked): array {
                $row = [
                    'id' => $location->id,
                    'name' => $location->name,
                    'handle' => $location->handle,
                    'default' => $location->default,
                    'stocked_variants_count' => (int) ($stocked[$location->id] ?? 0),
                    'urls' => [
                        'edit' => route('panel.settings.locations.edit', $location),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($location),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $location->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('settings/locations/Index', [
            'locations' => $locations,
            ...$this->tableProps($resolver, $this->columns, $request),
            'urls' => [
                'index' => route('panel.settings.locations.index'),
                'store' => route('panel.settings.locations.store'),
            ],
        ]);
    }
}
