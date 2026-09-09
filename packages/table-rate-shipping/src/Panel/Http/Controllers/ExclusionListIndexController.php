<?php

namespace Lunar\Shipping\Panel\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;
use Lunar\Shipping\Models\ShippingExclusionList;

class ExclusionListIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('shipping::exclusion_lists.column_name'), 'width' => 'minmax(0, 1.4fr)'],
            ['key' => 'exclusions_count', 'label' => __('shipping::exclusion_lists.column_products'), 'width' => '110px', 'align' => 'right'],
            ['key' => 'zones_count', 'label' => __('shipping::exclusion_lists.column_zones'), 'width' => '110px', 'align' => 'right'],
        ];

        $resolver = $this->resolveTable('shipping.exclusion-lists.index');

        $lists = ShippingExclusionList::query()
            ->withCount(['exclusions', 'shippingZones'])
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString()
            ->through(function (ShippingExclusionList $list) use ($resolver): array {
                $row = [
                    'id' => $list->id,
                    'name' => $list->name,
                    'exclusions_count' => (int) $list->getAttribute('exclusions_count'),
                    'zones_count' => (int) $list->getAttribute('shipping_zones_count'),
                    'urls' => [
                        'edit' => route('panel.settings.shipping.exclusion-lists.edit', $list),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($list),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $list->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('shipping::settings/shipping/exclusion-lists/Index', [
            'lists' => $lists,
            ...$this->tableProps($resolver, $this->columns, $request),
            'urls' => [
                'index' => route('panel.settings.shipping.exclusion-lists.index'),
                'store' => route('panel.settings.shipping.exclusion-lists.store'),
            ],
        ]);
    }
}
