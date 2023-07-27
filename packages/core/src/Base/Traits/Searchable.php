<?php

namespace Lunar\Base\Traits;

use Illuminate\Support\Arr;
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

    protected array $removeSearchableFields = [];

    protected array $removeFilterableFields = [];

    protected array $removeSortableFields = [];

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

        $data = array_merge(
            $this->filterable ?? [],
            $this->additionalFilterableFields,
        );

        return array_diff($data, $this->removeFilterableFields);
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
     * Remove additional fields to filter on.
     *
     * @return void
     */
    public function removeFilterableAttributes(array $removeAttributes)
    {
        $this->removeFilterableFields = array_merge($this->removeFilterableFields, $removeAttributes);
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
     * Remove additional sortable attributes.
     *
     * @return void
     */
    public function removeSortableAttributes(array $removeAttributes)
    {
        $this->removeSortableFields = array_merge($this->removeSortableFields, $removeAttributes);
    }

    /**
     * Return our base attributes we want sortable.
     *
     * @return array
     */
    public function getSortableAttributes()
    {
        $this->fireModelEvent('searchSetup');

        $data = array_merge(
            $this->sortable ?? [],
            $this->additionalSortableFields
        );

        return array_diff($data, $this->removeSortableFields);
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
    public function removeSearchableAttribute(array|string $keys)
    {
        $keys = Arr::wrap($keys);

        $this->removeSearchableFields = array_merge($this->removeSearchableFields, $keys);
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

        $data = array_merge(
            $this->getSearchableAttributes(),
            $this->additionalSearchableFields
        );

        foreach ($this->removeSearchableFields as $key) {
            unset($data[$key]);
        }

        return $data;
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
