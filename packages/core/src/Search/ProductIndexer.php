<?php

namespace Lunar\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductIndexer extends ScoutIndexer
{
    public function getSortableFields(): array
    {
        return [
            'created_at',
            'updated_at',
            'skus',
            'status',
        ];
    }

    public function getFilterableFields(): array
    {
        return [
            '__soft_deleted',
            'skus',
            'status',
        ];
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with([
            'thumbnail',
            'variants',
            'productType',
            'brand',
        ]);
    }

    public function toSearchableArray(Model $model): array
    {
        // Do this here so other additions to the data appear under the attributes,
        // more of a vanity thing than anything else.
        $data = array_merge([
            'id' => (string) $model->id,
            'status' => $model->status,
            'product_type' => $model->productType->name,
            'brand' => $model->brand?->name,
            'created_at' => (int) $model->created_at->timestamp,
        ], $this->mapSearchableAttributes($model));

        if ($thumbnail = $model->thumbnail) {
            $data['thumbnail'] = $thumbnail->getUrl('small');
        }

        $data['skus'] = $model->variants->pluck('sku')->toArray();

        return $data;
    }
}
