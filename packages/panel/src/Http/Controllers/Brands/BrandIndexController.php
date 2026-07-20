<?php

namespace Lunar\Panel\Http\Controllers\Brands;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Brand;
use Lunar\Core\States\Brand\Active;
use Lunar\Core\States\Brand\Draft;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class BrandIndexController
{
    use ResolvesTableExtensions;

    /** @var string[] */
    protected array $sortable = ['name', 'created_at', 'products_count'];

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('panel::brands.column_brand'), 'width' => 'minmax(0,1.4fr)'],
            ['key' => 'short_description', 'label' => __('panel::brands.column_description'), 'width' => 'minmax(0,1.6fr)'],
            ['key' => 'collections_count', 'label' => __('panel::brands.column_collections'), 'width' => '110px', 'align' => 'right'],
            ['key' => 'products_count', 'label' => __('panel::brands.column_products'), 'width' => '110px', 'align' => 'right'],
            ['key' => 'status', 'label' => __('panel::brands.column_status'), 'width' => '110px'],
        ];

        $sort = $request->string('sort')->value();
        $sort = in_array($sort, $this->sortable, true) ? $sort : 'created_at';

        $direction = $request->string('direction')->value() === 'asc' ? 'asc' : 'desc';

        $resolver = $this->resolveTable('brands.index');

        $brands = Brand::query()
            ->withCount(['products', 'collections'])
            ->with('thumbnail')
            ->when($request->filled('q'), function ($query) use ($request, $resolver) {
                $term = $request->string('q')->value();
                $like = "%{$term}%";

                $query->where(function ($query) use ($like, $term, $resolver) {
                    $query->where('name', 'like', $like)
                        ->orWhere('handle', 'like', $like)
                        ->orWhereHas('urls', fn ($query) => $query->where('slug', 'like', $like));

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
            ->through(function (Brand $brand) use ($resolver) {
                $row = [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'handle' => $brand->handle,
                    'thumbnail' => $brand->thumbnail?->getAvailableUrl(['small']),
                    'short_description' => $brand->translate('short_description'),
                    'collections_count' => (int) $brand->getAttribute('collections_count'),
                    'products_count' => (int) $brand->getAttribute('products_count'),
                    'status' => $brand->status->getValue(),
                    'status_label' => $brand->status->label(),
                    'created_at' => $brand->created_at,
                    'edit_url' => route('panel.brands.edit', $brand),
                    '_actions' => $resolver->resolveRowActionUrls($brand),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $brand->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('brands/Index', [
            'brands' => $brands,
            ...$this->tableProps($resolver, $this->columns, $request),
            'totalCount' => Brand::count(),
            'filters' => $request->only(['q', 'status', 'sort', 'direction']),
            'urls' => [
                'index' => route('panel.brands.index'),
                'create' => route('panel.brands.create'),
            ],
        ]);
    }
}
