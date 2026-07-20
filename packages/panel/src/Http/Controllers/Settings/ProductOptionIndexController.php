<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\ProductOption;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class ProductOptionIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('panel::product_options.column_name'), 'width' => 'minmax(0, 1.4fr)'],
            ['key' => 'handle', 'label' => __('panel::product_options.column_handle'), 'width' => 'minmax(0, 1fr)'],
            ['key' => 'values_count', 'label' => __('panel::product_options.column_values'), 'width' => '100px', 'align' => 'right'],
            ['key' => 'products_count', 'label' => __('panel::product_options.column_products'), 'width' => '100px', 'align' => 'right'],
        ];

        $resolver = $this->resolveTable('product-options.index');

        $productOptions = ProductOption::query()
            ->withCount(['values', 'products'])
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('handle')
            ->paginate(25)
            ->withQueryString()
            ->through(function (ProductOption $productOption) use ($resolver): array {
                $row = [
                    'id' => $productOption->id,
                    'name' => $productOption->translate('name'),
                    'handle' => $productOption->handle,
                    'shared' => $productOption->shared,
                    'values_count' => (int) $productOption->getAttribute('values_count'),
                    'products_count' => (int) $productOption->getAttribute('products_count'),
                    'urls' => [
                        'edit' => route('panel.settings.product-options.edit', $productOption),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($productOption),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $productOption->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('settings/product-options/Index', [
            'productOptions' => $productOptions,
            ...$this->tableProps($resolver, $this->columns, $request),
            'urls' => [
                'index' => route('panel.settings.product-options.index'),
                'store' => route('panel.settings.product-options.store'),
            ],
        ]);
    }
}
