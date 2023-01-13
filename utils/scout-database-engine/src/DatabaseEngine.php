<?php

namespace Lunar\ScoutDatabaseEngine;

use Illuminate\Support\Arr;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;

class DatabaseEngine extends Engine
{
    /**
     * Determines if soft deletes for Scout are enabled or not.
     *
     * @var bool
     */
    protected $softDelete;

    /**
     * Create a new engine instance.
     *
     * @param  bool  $softDelete
     * @return void
     */
    public function __construct($softDelete = false)
    {
        $this->softDelete = $softDelete;
    }

    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function update($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        $objects = $models->each(function ($model) {
            // Clear existing index data
            SearchIndex::where('key', '=', $model->getScoutKey())
                ->where('index', '=', $model->searchableAs())
                ->delete();

            if (empty($searchableData = $model->toSearchableArray())) {
                return;
            }

            $indexes = collect($searchableData)
                ->filter(function ($data) {
                    return ! is_null($data);
                })
                ->map(function ($data, $field) use ($model) {
                    if (is_iterable($data)) {
                        $data = implode(' , ', Arr::flatten($data));
                    }

                    return [
                        'key' => $model->getScoutKey(),
                        'index' => $model->searchableAs(),
                        'field' => $field,
                        'content' => $data,
                    ];
                });

            SearchIndex::insert($indexes->values()->all());

            return $indexes;
        });
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function delete($models)
    {
        $models->each(function ($model) {
            // Clear existing index data
            SearchIndex::where('key', '=', $model->getScoutKey())
                ->where('index', '=', $model->searchableAs())
                ->delete();
        });
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @return mixed
     */
    public function search(Builder $builder)
    {
        return $this->getSearchQuery($builder)->get();
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  int  $perPage
     * @param  int  $page
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        $offset = $perPage * ($page - 1);

        return $this->getSearchQuery($builder)
            ->limit($perPage)
            ->offset($offset)
            ->get();
    }

    protected function getSearchQuery(Builder $builder)
    {
        $index = $this->getIndexFromBuilder($builder);

        return SearchIndex::where('index', '=', $index)
            ->whereFullText('content', $builder->query . '*', ['mode' => 'boolean']);
    }

    /**
     * Pluck and return the primary keys of the given results.
     *
     * @param  mixed  $results
     * @return \Illuminate\Support\Collection
     */
    public function mapIds($results)
    {
        return $results->pluck('key')->all();
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  mixed  $results
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function map(Builder $builder, $results, $model)
    {
        if ($results === null) {
            return $model->newCollection();
        }

        $objectIds = $results->pluck('key')->all();

        return $model->getScoutModelsByIds(
            $builder,
            $objectIds
        )->filter(function ($model) use ($objectIds) {
            return in_array($model->getScoutKey(), $objectIds);
            // })->sortBy(function ($model) use ($objectIdPositions) {
        //     return $objectIdPositions[$model->getScoutKey()];
        })->values();
    }

    /**
     * Map the given results to instances of the given model via a lazy collection.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  mixed  $results
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Support\LazyCollection
     */
    public function lazyMap(Builder $builder, $results, $model)
    {
        //
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param  mixed  $results
     * @return int
     */
    public function getTotalCount($results)
    {
        return $results ? $results->unique()->count() : 0;
    }

    /**
     * Flush all of the model's records from the engine.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function flush($model)
    {
        SearchIndex::where('index', '=', $model->searchableAs())->delete();
    }

    /**
     * Create a search index.
     *
     * @param  string  $name
     * @param  array  $options
     * @return mixed
     *
     * @throws \Exception
     */
    public function createIndex($name, array $options = [])
    {
        //
    }

    /**
     * Delete a search index.
     *
     * @param  string  $name
     * @return mixed
     */
    public function deleteIndex($name)
    {
        //
    }

    /**
     * Gets the index name to use.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @return string
     */
    protected function getIndexFromBuilder(Builder $builder)
    {
        return $builder->index ?: $builder->model->searchableAs();
    }
}
