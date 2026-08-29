<?php

namespace Lunar\Panel\Search\Sources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Brand;
use Lunar\Panel\Search\SearchSource;
use Lunar\Panel\Sections\Catalog\CatalogSection;
use Lunar\Panel\Support\Position;

class BrandSearchSource extends SearchSource
{
    public function key(): string
    {
        return 'brands';
    }

    public function label(): string
    {
        return __('panel::search.source_brands');
    }

    public function icon(): string
    {
        return 'tag';
    }

    public function permission(): string
    {
        return CatalogSection::BRANDS_PERMISSION;
    }

    public function position(): Position
    {
        return Position::priority(40);
    }

    /** @return Builder<Brand> */
    public function query(): Builder
    {
        return Brand::query()->withCount('products');
    }

    public function applyTerm(Builder $query, string $token): void
    {
        $like = "%{$token}%";

        $query->where('name', 'like', $like)
            ->orWhere('handle', 'like', $like)
            ->orWhereHas('urls', fn (Builder $query) => $query->where('slug', 'like', $like));
    }

    /** @param Brand $model */
    public function row(Model $model): array
    {
        return [
            'id' => $model->id,
            'label' => (string) $model->name,
            'hint' => __('panel::search.hint_product_count', ['count' => (int) $model->getAttribute('products_count')]),
            'url' => route('panel.brands.edit', $model),
        ];
    }
}
