<?php

namespace Lunar\Base\Traits;

use Laravel\Scout\EngineManager;
use Laravel\Scout\Searchable as ScoutSearchable;

trait Searchable
{
    use ScoutSearchable;

    /**
     * Define the additional fields we want to index.
     *
     * @var array
     */
    protected $additionalSearchableFields = [];

    /**
     * Define the additional filterable fields.
     *
     * @var array
     */
    protected $additionalFilterableFields = [];

    /**
     * Define the additional sortable fields.
     *
     * @var array
     */
    protected $additionalSortableFields = [];

    /**
     * Return our base (core) attributes we want searchable.
     *
     * @return array
     */
    public function getSearchableAttributes()
    {
        return [
            'id' => $this->id,
        ];
    }

    /**
     * Return our base attributes we want filterable.
     *
     * @return array
     */
    public function getFilterableAttributes()
    {
        $this->fireModelEvent('searchSetup');

        return array_merge(
            $this->filterable ?? [],
            $this->additionalFilterableFields,
        );
    }

    /**
     * Add additional fields to filter on.
     *
     * @return void
     */
    public function addFilterableAttributes(array $attributes)
    {
        collect($attributes)->filter(function ($att) {
            return ! in_array($att, $this->filterable) && ! in_array($att, $this->additionalFilterableFields);
        })->each(function ($att) {
            $this->additionalFilterableFields[] = $att;
        });
    }

    /**
     * Add additional sortable attributes.
     *
     * @return void
     */
    public function addSortableAttributes(array $attributes)
    {
        collect($attributes)->filter(function ($att) {
            return ! in_array($att, $this->sortable) && ! in_array($att, $this->additionalSortableFields);
        })->each(function ($att) {
            $this->additionalSortableFields[] = $att;
        });
    }

    /**
     * Return our base attributes we want sortable.
     *
     * @return array
     */
    public function getSortableAttributes()
    {
        $this->fireModelEvent('searchSetup');

        return array_merge(
            $this->sortable ?? [],
            $this->additionalSortableFields
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getObservableEvents()
    {
        return array_merge(parent::getObservableEvents(), [
            'indexing',
            'searchSetup',
        ]);
    }

    /**
     * Add an attribute into the additional searchable fields.
     *
     * @param  string  $key
     * @param  string|mixed  $value
     * @return void
     */
    public function addSearchableAttribute($key, $value)
    {
        $this->additionalSearchableFields[$key] = $value;
    }

    /**
     * Remove an attribute from additional searchable fields.
     *
     * @param  string  $key
     * @return void
     */
    public function removeSearchableAttribute($key)
    {
        $baseAttributes = $this->getSearchableAttributes();

        if (! isset($baseAttributes[$key])) {
            unset($this->additionalSearchableFields[$key]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function toSearchableArray()
    {
        if (config('scout.driver') == 'mysql') {
            return $this->only(array_keys($this->getAttributes()));
        }

        $this->fireModelEvent('indexing');

        return array_merge(
            $this->getSearchableAttributes(),
            $this->additionalSearchableFields
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
