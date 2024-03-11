<?php

namespace Lunar\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BrandIndexer extends ScoutIndexer
{
    public function getSortableFields(): array
    {
        return [
            'created_at',
            'updated_at',
            'name',
        ];
    }

    public function getFilterableFields(): array
    {
        return [
            '__soft_deleted',
            'name',
        ];
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query;
    }

    public function toSearchableArray(Model $model): array
    {
        return array_merge([
            'id' => (string) $model->id,
            'name' => $model->name,
            'created_at' => (int) $model->created_at->timestamp,
        ], $this->mapSearchableAttributes($model));
    }
}
