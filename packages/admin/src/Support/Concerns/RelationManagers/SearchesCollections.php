<?php

namespace Lunar\Admin\Support\Concerns\RelationManagers;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Builder as ScoutBuilder;
use Lunar\Models\Collection;
use Lunar\Models\Contracts\Collection as CollectionContract;

trait SearchesCollections
{
    /**
     * @return array<int, string>
     */
    protected static function getCollectionSearchResults(string $search, int $limit): array
    {
        return static::withCollectionPathRelations(
            get_search_builder(Collection::modelClass(), $search)->take($limit)
        )
            ->get()
            ->mapWithKeys(fn (CollectionContract $record): array => [
                $record->getKey() => static::getCollectionOptionLabel($record),
            ])
            ->all();
    }

    /**
     * The full path to a collection, e.g. "Main > Clothing > Tops".
     */
    protected static function getCollectionOptionLabel(CollectionContract $record): string
    {
        return collect([static::getCollectionPath($record), $record->attr('name')])
            ->filter()
            ->implode(' > ');
    }

    /**
     * The group and ancestors leading to a collection, e.g. "Main > Clothing".
     */
    protected static function getCollectionPath(CollectionContract $record): string
    {
        return collect([$record->group?->name])
            ->merge($record->breadcrumb)
            ->filter()
            ->implode(' > ');
    }

    /**
     * A path label reads the group and every ancestor, so left lazy it costs two
     * queries for each collection being labelled.
     */
    protected static function withCollectionPathRelations(ScoutBuilder|Builder $builder): ScoutBuilder|Builder
    {
        $relations = ['group', 'ancestors'];

        return $builder instanceof ScoutBuilder
            ? $builder->query(fn (Builder $query) => $query->with($relations))
            : $builder->with($relations);
    }
}
