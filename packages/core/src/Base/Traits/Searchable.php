<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Searchable as ScoutSearchable;
use Lunar\Search\ScoutIndexer;

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
