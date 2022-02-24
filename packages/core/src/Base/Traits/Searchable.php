<?php

namespace GetCandy\Base\Traits;

use Laravel\Scout\Searchable as ScoutSearchable;

trait Searchable
{
    use ScoutSearchable;

    /**
     * Define the additional fields we want to index.
     *
     * @var array
     */
    protected $additionalSearchFields = [];

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
     * Return out base attributes we want filterable.
     *
     * @return array
     */
    public function getFilterableAttributes()
    {
        return [
            'status',
            'placed_at',
            'created_at',
            'total',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getObservableEvents()
    {
        return array_merge(parent::getObservableEvents(), [
            'indexing',
        ]);
    }

    /**
     * Add an attribute into the additional searchable fields.
     *
     * @param string $key
     * @param string|mixed $value
     * @return void
     */
    public function addSearchableAttribute($key, $value)
    {
        if (!isset($this->additionalSearchFields[$key])) {
            $this->additionalSearchFields[$key] = $value;
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
            $this->additionalSearchFields
        );
    }
}
