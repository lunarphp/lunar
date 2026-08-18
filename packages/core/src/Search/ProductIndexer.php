<?php

namespace Lunar\Core\Search;

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
            'variants' => fn ($query) => $query->select('id', 'sku', 'product_id'),
            'productType' => fn ($query) => $query->select('id', 'name'),
            'brand' => fn ($query) => $query->select('id', 'name'),
        ]);
    }

    public function toSearchableArray(Model $model): array
    {
        // Scout's database engine calls this on an empty model prototype to
        // derive the searchable columns, so every relation and date here must
        // be null-safe.
        // Do this here so other additions to the data appear under the attributes,
        // more of a vanity thing than anything else.
        $data = array_merge([
            'id' => (string) $model->id,
            'public_id' => (string) $model->public_id,
            'status' => (string) $model->status,
            'product_type' => $model->productType?->name,
            'brand' => $model->brand?->name,
            'created_at' => (int) $model->created_at?->timestamp,
        ],
            $this->mapTranslatableFields($model, ['name', 'description', 'short_description']),
            $this->mapSearchableAttributes($model),
        );

        if ($thumbnail = $model->thumbnail) {
            $data['thumbnail'] = $thumbnail->getUrl('small');
        }

        $data['skus'] = $model->variants->pluck('sku')->toArray();

        return $data;
    }
}
