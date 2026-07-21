<?php

namespace Lunar\Panel\Http\Controllers\ProductTypes;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\ProductType;
use Lunar\Core\States\ProductType\Active;
use Lunar\Core\States\ProductType\Draft;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class ProductTypeIndexController
{
    use ResolvesTableExtensions;

    /** @var string[] */
    protected array $sortable = ['name', 'created_at', 'products_count'];

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('panel::product-types.column_product_type'), 'width' => 'minmax(0,1.4fr)'],
            ['key' => 'description', 'label' => __('panel::product-types.column_description'), 'width' => 'minmax(0,1.6fr)'],
            ['key' => 'attributes_count', 'label' => __('panel::product-types.column_attributes'), 'width' => '140px', 'align' => 'right'],
            ['key' => 'products_count', 'label' => __('panel::product-types.column_products'), 'width' => '110px', 'align' => 'right'],
            ['key' => 'status', 'label' => __('panel::product-types.column_status'), 'width' => '110px'],
        ];

        $sort = $request->string('sort')->value();
        $sort = in_array($sort, $this->sortable, true) ? $sort : 'created_at';

        $direction = $request->string('direction')->value() === 'asc' ? 'asc' : 'desc';

        $resolver = $this->resolveTable('product-types.index');

        $productTypes = ProductType::query()
            ->withCount(['products', 'productAttributes', 'variantAttributes'])
            ->when($request->filled('q'), function ($query) use ($request, $resolver) {
                $term = $request->string('q')->value();
                $like = "%{$term}%";

                $query->where(function ($query) use ($like, $term, $resolver) {
                    $query->where('name', 'like', $like)
                        ->orWhere('handle', 'like', $like);

                    $resolver->applySearchQueries($query, $term);
                });
            })
            ->when(
                in_array($request->string('status')->value(), [Active::$name, Draft::$name], true),
                fn ($query) => $query->where('status', $request->string('status')->value()),
            )
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(function (ProductType $productType) use ($resolver) {
                $row = [
                    'id' => $productType->id,
                    'name' => $productType->name,
                    'handle' => $productType->handle,
                    'description' => $productType->description,
                    'product_attributes_count' => (int) $productType->getAttribute('product_attributes_count'),
                    'variant_attributes_count' => (int) $productType->getAttribute('variant_attributes_count'),
                    'products_count' => (int) $productType->getAttribute('products_count'),
                    'status' => $productType->status->getValue(),
                    'status_label' => $productType->status->label(),
                    'created_at' => $productType->created_at,
                    'edit_url' => route('panel.product-types.edit', $productType),
                    '_actions' => $resolver->resolveRowActionUrls($productType),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $productType->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('product-types/Index', [
            'productTypes' => $productTypes,
            ...$this->tableProps($resolver, $this->columns, $request),
            'totalCount' => ProductType::count(),
            'filters' => $request->only(['q', 'status', 'sort', 'direction']),
            'urls' => [
                'index' => route('panel.product-types.index'),
                'create' => route('panel.product-types.create'),
            ],
        ]);
    }
}
