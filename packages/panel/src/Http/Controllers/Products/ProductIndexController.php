<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Tag;
use Lunar\Core\States\Product\Archived;
use Lunar\Core\States\Product\Draft;
use Lunar\Core\States\Product\Published;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class ProductIndexController
{
    use ResolvesTableExtensions;

    /** @var string[] */
    protected array $sortable = ['name', 'created_at', 'stock'];

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('panel::products.column_product'), 'width' => 'minmax(0,1.6fr)'],
            ['key' => 'status', 'label' => __('panel::products.column_status'), 'width' => '110px'],
            ['key' => 'brand', 'label' => __('panel::products.column_brand'), 'width' => 'minmax(0,0.8fr)'],
            ['key' => 'sku', 'label' => __('panel::products.column_sku'), 'width' => 'minmax(0,1fr)'],
            ['key' => 'stock', 'label' => __('panel::products.column_stock'), 'width' => '90px', 'align' => 'right'],
            ['key' => 'product_type', 'label' => __('panel::products.column_type'), 'width' => 'minmax(0,0.8fr)'],
            ['key' => 'tags', 'label' => __('panel::products.column_tags'), 'width' => 'minmax(0,0.9fr)'],
        ];

        $sort = $request->string('sort')->value();
        $sort = in_array($sort, $this->sortable, true) ? $sort : 'created_at';

        $direction = $request->string('direction')->value() === 'asc' ? 'asc' : 'desc';

        $resolver = $this->resolveTable('products.index');

        $statuses = [Published::$name, Draft::$name, Archived::$name];

        $products = Product::query()
            ->with(['thumbnail', 'brand:id,name', 'productType:id,name', 'tags', 'variants:id,product_id,sku'])
            ->withSum('variants as stock', 'stock_available')
            ->when($request->filled('q'), function ($query) use ($request, $resolver) {
                $term = $request->string('q')->value();
                $like = "%{$term}%";

                $query->where(function ($query) use ($like, $term, $resolver) {
                    // The dedicated name column holds a {locale: text} map.
                    $query->where('name', 'like', $like)
                        ->orWhereHas('variants', fn ($query) => $query->where('sku', 'like', $like))
                        ->orWhereHas('urls', fn ($query) => $query->where('slug', 'like', $like));

                    $resolver->applySearchQueries($query, $term);
                });
            })
            ->when(
                in_array($request->string('status')->value(), $statuses, true),
                fn ($query) => $query->where('status', $request->string('status')->value()),
            )
            ->when(
                $request->filled('brand'),
                fn ($query) => $query->where('brand_id', $request->integer('brand')),
            )
            ->when(
                $request->filled('type'),
                fn ($query) => $query->where('product_type_id', $request->integer('type')),
            )
            ->when(
                $request->filled('tag'),
                fn ($query) => $query->whereHas('tags', fn ($query) => $query->where('value', $request->string('tag')->value())),
            )
            ->when(
                $request->string('stock_state')->value() === 'out',
                fn ($query) => $query->whereDoesntHave('variants', fn ($query) => $query->where('stock_available', '>', 0)),
            )
            ->when(
                $request->string('stock_state')->value() === 'in',
                fn ($query) => $query->whereHas('variants', fn ($query) => $query->where('stock_available', '>', 0)),
            )
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy($sort === 'stock' ? 'stock' : $sort, $direction)
            ->paginate(15)
            ->withQueryString();

        $orderedProductIds = $this->orderedProductIds($products->getCollection()->modelKeys());

        $products->through(function (Product $product) use ($resolver, $orderedProductIds) {
            $product->setAttribute('has_order_history', in_array($product->id, $orderedProductIds, true));

            $row = [
                'id' => $product->id,
                'name' => $product->translate('name'),
                'thumbnail' => $product->thumbnail?->getAvailableUrl(['small']),
                'status' => $product->status->getValue(),
                'status_label' => $product->status->label(),
                'brand' => $product->brand?->name,
                'sku' => $product->variants->first()?->sku,
                'extra_sku_count' => max(0, $product->variants->count() - 1),
                'stock' => (int) $product->getAttribute('stock'),
                'product_type' => $product->productType->name,
                'tags' => $product->tags->pluck('value')->values()->all(),
                'created_at' => $product->created_at,
                'edit_url' => route('panel.products.edit', $product),
                '_actions' => $resolver->resolveRowActionUrls($product),
            ];

            foreach ($resolver->getColumnKeys() as $key) {
                $row[$key] = $product->getAttribute($key);
            }

            return $row;
        });

        return Inertia::render('products/Index', [
            'products' => $products,
            ...$this->tableProps($resolver, $this->columns, $request),
            'totalCount' => Product::count(),
            'kpis' => $this->kpis(),
            'brandOptions' => Brand::query()->orderBy('name')->get(['id', 'name'])
                ->map(fn (Brand $brand) => ['value' => (string) $brand->id, 'label' => $brand->name]),
            'typeOptions' => ProductType::query()->orderBy('name')->get(['id', 'name'])
                ->map(fn (ProductType $type) => ['value' => (string) $type->id, 'label' => $type->name]),
            'tagOptions' => Tag::query()->orderBy('value')->pluck('value')
                ->map(fn (string $value) => ['value' => $value, 'label' => $value]),
            'filters' => $request->only(['q', 'status', 'brand', 'type', 'tag', 'stock_state', 'sort', 'direction']),
            'urls' => [
                'index' => route('panel.products.index'),
                'create' => route('panel.products.create'),
            ],
        ]);
    }

    /**
     * Ids among the given products with at least one ordered variant — one
     * query for the page instead of a per-row exists check.
     *
     * @param  array<int, int>  $productIds
     * @return array<int, int>
     */
    protected function orderedProductIds(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        $variants = (new ProductVariant)->getTable();

        return OrderLine::query()
            ->where('purchasable_type', (new ProductVariant)->getMorphClass())
            ->join($variants, "{$variants}.id", '=', 'purchasable_id')
            ->whereIn("{$variants}.product_id", $productIds)
            ->distinct()
            ->pluck("{$variants}.product_id")
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return array{total: int, published: int, draft: int, outOfStock: int}
     */
    protected function kpis(): array
    {
        return Cache::remember('lunar.panel.products.kpis', now()->addMinutes(5), fn (): array => [
            'total' => Product::count(),
            'published' => Product::query()->where('status', Published::$name)->count(),
            'draft' => Product::query()->where('status', Draft::$name)->count(),
            'outOfStock' => Product::query()
                ->whereDoesntHave('variants', fn ($query) => $query->where('stock_available', '>', 0))
                ->count(),
        ]);
    }
}
