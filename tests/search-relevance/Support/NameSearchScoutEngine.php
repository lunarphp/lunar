<?php

namespace Lunar\Tests\SearchRelevance\Support;

use Laravel\Scout\Builder;
use Laravel\Scout\Engines\DatabaseEngine;

/**
 * Scout's database engine matches every key of toSearchableArray() against a
 * column, which Product's indexer does not satisfy. Match the name only.
 */
final class NameSearchScoutEngine extends DatabaseEngine
{
    protected function buildSearchQuery(Builder $builder)
    {
        $query = $builder->model->newQuery()->orderBy($builder->model->getKeyName());

        if (filled($builder->query)) {
            $query->where('name', 'like', '%'.$builder->query.'%');
        }

        return $this->constrainForSoftDeletes(
            $builder, $this->addAdditionalConstraints($builder, $query->take($builder->limit))
        );
    }
}
