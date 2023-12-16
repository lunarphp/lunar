<?php

namespace Lunar\Search;

use Illuminate\Database\Eloquent\Builder;

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
}
