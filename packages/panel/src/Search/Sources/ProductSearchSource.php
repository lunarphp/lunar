<?php

namespace Lunar\Panel\Search\Sources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Product;
use Lunar\Panel\Search\SearchSource;
use Lunar\Panel\Sections\Catalog\CatalogSection;
use Lunar\Panel\Support\Position;

class ProductSearchSource extends SearchSource
{
    public function key(): string
    {
        return 'products';
    }

    public function label(): string
    {
        return __('panel::search.source_products');
    }

    public function icon(): string
    {
        return 'box';
    }

    public function permission(): string
    {
        return CatalogSection::PRODUCTS_PERMISSION;
    }

    public function position(): Position
    {
        return Position::priority(20);
    }

    /** @return Builder<Product> */
    public function query(): Builder
    {
        return Product::query()->with(['brand:id,name', 'variants:id,product_id,sku']);
    }

    public function applyTerm(Builder $query, string $token): void
    {
        $like = "%{$token}%";

        // The dedicated name column holds a {locale: text} map.
        $query->where('name', 'like', $like)
            ->orWhereHas('variants', fn (Builder $query) => $query->where('sku', 'like', $like))
            ->orWhereHas('urls', fn (Builder $query) => $query->where('slug', 'like', $like));
    }

    /** @param Product $model */
    public function row(Model $model): array
    {
        $sku = $model->variants->first()?->sku;

        return [
            'id' => $model->id,
            'label' => (string) $model->translate('name'),
            'hint' => implode(' · ', array_filter([$sku, $model->brand?->name])) ?: null,
            'url' => route('panel.products.edit', $model),
        ];
    }
}
