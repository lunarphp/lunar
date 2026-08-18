<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Enums\ProductOptionType;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class ProductOptionIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('panel::product_options.column_name'), 'width' => 'minmax(0, 1.2fr)'],
            ['key' => 'type', 'label' => __('panel::product_options.column_type'), 'width' => '120px'],
            ['key' => 'values', 'label' => __('panel::product_options.column_values'), 'width' => 'minmax(0, 1.6fr)'],
            ['key' => 'products_count', 'label' => __('panel::product_options.column_products'), 'width' => '100px', 'align' => 'right'],
        ];

        $collection = config('lunar.media.collection');

        $type = $request->string('type')->toString() ?: null;
        $unused = $request->boolean('unused');
        // Shared-only is the default view; dedicated (product-local) options are
        // hidden until the toggle is switched off (?shared=0).
        $sharedOnly = $request->has('shared') ? $request->boolean('shared') : true;

        $resolver = $this->resolveTable('product-options.index');

        $productOptions = ProductOption::query()
            ->withCount(['values', 'products'])
            ->with(['values' => fn ($query) => $query->orderBy('position'), 'values.media'])
            ->when($sharedOnly, fn ($query) => $query->shared())
            ->when($type, fn ($query) => $query->type($type))
            ->when($unused, fn ($query) => $query->whereDoesntHave('products'))
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('handle')
            ->paginate(25)
            ->withQueryString()
            ->through(function (ProductOption $productOption) use ($resolver, $collection): array {
                $row = [
                    'id' => $productOption->id,
                    'name' => $productOption->translate('name'),
                    'handle' => $productOption->handle,
                    'type' => $productOption->type,
                    'shared' => $productOption->shared,
                    'values_count' => (int) $productOption->getAttribute('values_count'),
                    'values_preview' => $productOption->values->take(4)->map(fn (ProductOptionValue $value) => [
                        'name' => $value->translate('name'),
                        'colour' => $value->meta['colour'] ?? null,
                        'swatch' => $value->getFirstMediaUrl($collection, 'small') ?: ($value->getFirstMediaUrl($collection) ?: null),
                    ])->values(),
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
            'typeOptions' => collect(ProductOptionType::cases())
                ->map(fn (ProductOptionType $case) => ['value' => $case->value, 'icon' => $case->icon()])
                ->values(),
            'filters' => [
                'type' => $type,
                'unused' => $unused,
                'sharedOnly' => $sharedOnly,
            ],
            'defaultLocale' => Language::query()->where('default', true)->value('code') ?? app()->getLocale(),
            ...$this->tableProps($resolver, $this->columns, $request),
            'urls' => [
                'index' => route('panel.settings.product-options.index'),
                'store' => route('panel.settings.product-options.store'),
            ],
        ]);
    }
}
