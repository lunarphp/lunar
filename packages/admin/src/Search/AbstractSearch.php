<?php

namespace GetCandy\Hub\Search;

use GetCandy\Hub\Base\SearchInterface;
use GetCandy\Hub\DataTransferObjects\Search\SearchResults;
use Illuminate\Database\Eloquent\Model;

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
     * Return the right driver for the model
     *
     * @param  string  $model
     * @return string
     */    
    public function getDriverForModel(string $model): string
    {
        $engines = config('getcandy.search.engine_map', []);
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
