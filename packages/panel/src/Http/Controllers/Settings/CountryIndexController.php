<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Country;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class CountryIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'iso2', 'label' => __('panel::countries.column_iso2'), 'width' => '90px'],
            ['key' => 'iso3', 'label' => __('panel::countries.column_iso3'), 'width' => '90px'],
            ['key' => 'name', 'label' => __('panel::countries.column_name'), 'width' => 'minmax(0, 1.6fr)'],
            ['key' => 'states_count', 'label' => __('panel::countries.column_states'), 'width' => '90px', 'align' => 'right'],
        ];

        $resolver = $this->resolveTable('countries.index');

        $countries = Country::query()
            ->withCount('states')
            ->when($request->filled('q'), function ($query) use ($request, $resolver) {
                $term = $request->string('q')->value();
                $like = "%{$term}%";

                $query->where(function ($query) use ($like, $term, $resolver) {
                    $query->where('name', 'like', $like)
                        ->orWhere('iso2', 'like', $like)
                        ->orWhere('iso3', 'like', $like);

                    $resolver->applySearchQueries($query, $term);
                });
            })
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString()
            ->through(function (Country $country) use ($resolver): array {
                $row = [
                    'id' => $country->id,
                    'name' => $country->name,
                    'iso2' => $country->iso2,
                    'iso3' => $country->iso3,
                    'emoji' => $country->emoji,
                    'states_count' => (int) $country->getAttribute('states_count'),
                    'urls' => [
                        'edit' => route('panel.settings.countries.edit', $country),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($country),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $country->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('settings/countries/Index', [
            'countries' => $countries,
            ...$this->tableProps($resolver, $this->columns, $request),
            'filters' => $request->only(['q']),
            'urls' => [
                'index' => route('panel.settings.countries.index'),
            ],
        ]);
    }
}
