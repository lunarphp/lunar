<?php

namespace GetCandy\Hub\Search;

use GetCandy\Hub\Base\SearchInterface;
use GetCandy\Hub\DataTransferObjects\Search\SearchResults;

abstract class AbstractSearch implements SearchInterface
{
    /**
     * Initialise the class
     */
    public function __construct()
    {
        $this->driver = config('scout.driver');
    }

    /**
     * Return search results from given criteria
     *
     * @param string $term
     * @param array $options
     * @param integer $perPage
     * @param integer $page
     * @return SearchResults
     */
    abstract public function search($term, $options = [], $perPage = 25, $page = 1): SearchResults;
}
