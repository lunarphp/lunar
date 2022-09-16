<?php

namespace Lunar\Hub\Search;

use Illuminate\Database\Eloquent\Model;
use Lunar\Hub\Base\SearchInterface;
use Lunar\Hub\DataTransferObjects\Search\SearchResults;

abstract class AbstractSearch implements SearchInterface
{
    /**
     * Initialise the class.
     */
    public function __construct()
    {
        $this->driver = config('scout.driver');
    }

    /**
     * Return the right driver for the model.
     *
     * @param  string  $model
     * @return string
     */
    public function getDriverForModel(string $model): string
    {
        $engines = config('lunar.search.engine_map', []);
        if (isset($engines[$model])) {
            return $engines[$model];
        }

        return $this->driver;
    }

    /**
     * Return search results from given criteria.
     *
     * @param  string  $term
     * @param  array  $options
     * @param  int  $perPage
     * @param  int  $page
     * @return SearchResults
     */
    abstract public function search($term, $options = [], $perPage = 25, $page = 1): SearchResults;
}
