<?php

namespace Lunar\Core\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CollectionIndexer extends ScoutIndexer
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
            'public_id' => (string) $model->public_id,
            'created_at' => (int) $model->created_at->timestamp,
        ],
            $this->mapTranslatableFields($model, ['name', 'description', 'short_description']),
            $this->mapSearchableAttributes($model),
        );
    }
}
