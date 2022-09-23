<?php

namespace Lunar\Hub\Base;

use Lunar\Hub\DataTransferObjects\Search\SearchResults;

interface SearchInterface
{
    /**
     * Return search results from given criteria.
     *
     * @param  string  $term
     * @param  array  $options
     * @param  int  $perPage
     * @param  int  $page
     * @return SearchResults
     */
    public function search($term, $options = [], $perPage = 25, $page = 1): SearchResults;
}
