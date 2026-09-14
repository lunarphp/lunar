<?php

use Illuminate\Support\HtmlString;
use Lunar\Search\Data\SearchHit;
use Lunar\Search\Data\SearchResults;

if (! function_exists('lunar_search_attrs')) {
    /**
     * The data attributes the storefront tracking script reads from each
     * rendered result. Empty when the search was not logged.
     */
    function lunar_search_attrs(SearchResults $results, SearchHit $hit): HtmlString
    {
        $searchId = $results->meta['search_id'] ?? null;
        $productId = $hit->document['id'] ?? null;

        if (! $searchId || $productId === null) {
            return new HtmlString('');
        }

        $attributes = [
            'data-lunar-search-id' => $searchId,
            'data-lunar-product-id' => $productId,
            'data-lunar-position' => $hit->meta['position'] ?? '',
            'data-lunar-source' => $hit->meta['source'] ?? 'organic',
        ];

        return new HtmlString(collect($attributes)
            ->map(fn ($value, $name) => $name.'="'.e((string) $value).'"')
            ->join(' '));
    }
}
