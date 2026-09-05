<?php

namespace Lunar\Tests\Panel\Fixtures\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\ProductType;
use Lunar\Panel\Search\SearchSource;

/**
 * A source over a model that carries no Scout index, standing in for an add-on
 * entity in a store that has the Scout path switched on.
 */
class ProductTypeSearchSource extends SearchSource
{
    public function key(): string
    {
        return 'product-types';
    }

    public function label(): string
    {
        return 'Product types';
    }

    /** @return Builder<ProductType> */
    public function query(): Builder
    {
        return ProductType::query();
    }

    public function applyTerm(Builder $query, string $token): void
    {
        $query->where('name', 'like', "%{$token}%");
    }

    /** @param ProductType $model */
    public function row(Model $model): array
    {
        return [
            'id' => $model->id,
            'label' => (string) $model->name,
            'hint' => null,
            'url' => route('panel.product-types.edit', $model),
        ];
    }
}
