<?php

namespace Lunar\Core\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as BaseCollection;
use Laravel\Scout\Builder as ScoutBuilder;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\CollectionEngine;
use Laravel\Scout\Engines\DatabaseEngine;
use Laravel\Scout\Searchable as ScoutSearchable;
use Lunar\Core\Search\ScoutIndexer;

trait Searchable
{
    use ScoutSearchable;

    /**
     * Return our base attributes we want filterable.
     *
     * @return array
     */
    public function getFilterableAttributes()
    {
        return $this->indexer()->getFilterableFields();
    }

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return $this->indexer()->searchableAs($this);
    }

    /**
     * Return our base attributes we want sortable.
     *
     * @return array
     */
    public function getSortableAttributes()
    {
        return $this->indexer()->getSortableFields();
    }

    /**
     * Get the value used to index the model.
     */
    public function getScoutKey(): mixed
    {
        return $this->indexer()->getScoutKey($this);
    }

    /**
     * Get the key name used to index the model.
     */
    public function getScoutKeyName(): mixed
    {
        return $this->indexer()->getScoutKeyName($this);
    }

    public function shouldBeSearchable()
    {
        return $this->indexer()->shouldBeSearchable($this);
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $this->indexer()->makeAllSearchableUsing($query);
    }

    /**
     * Only the collection engine builds documents from the models returned
     * here, when it filters a search. Other engines receive the caller's
     * models, so loading relations here would only load them twice. The
     * relations load onto copies so the caller's models never carry the
     * indexer's partial selects.
     */
    public function makeSearchableUsing(BaseCollection $models)
    {
        if (! $this->searchableUsing() instanceof CollectionEngine) {
            return $models;
        }

        $relations = $this->makeAllSearchableUsing($this->newQuery())->getEagerLoads();

        return EloquentCollection::make($models->map(fn ($model) => clone $model))->loadMissing($relations);
    }

    /**
     * Scout's database engine ORs a text match across the search columns
     * inside one nested where. With no columns the nested where is empty,
     * Laravel drops it, and every row would match.
     */
    public function newScoutQuery(ScoutBuilder $builder): Builder
    {
        return $this->newQuery()->when(
            filled($builder->query) && $this->indexer()->getDatabaseSearchColumns($this) === [],
            fn (Builder $query) => $query->whereRaw('1 = 0'),
        );
    }

    public function indexer()
    {
        $config = config('lunar.search.indexers', []);

        return app($config[self::class] ?? ScoutIndexer::class);
    }

    /**
     * {@inheritDoc}
     */
    public function toSearchableArray()
    {
        if ($this->searchableUsing() instanceof DatabaseEngine) {
            return array_fill_keys($this->indexer()->getDatabaseSearchColumns($this), null);
        }

        return $this->indexer()->toSearchableArray(
            $this,
            config('scout.driver')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function searchableUsing()
    {
        $engines = config('lunar.search.engine_map', []);

        if (isset($engines[self::class])) {
            return app(EngineManager::class)->engine(
                $engines[self::class]
            );
        }

        return app(EngineManager::class)->engine();
    }
}
