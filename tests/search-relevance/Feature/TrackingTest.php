<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Lunar\Search\Data\SearchHit;
use Lunar\Search\Data\SearchResults;
use Lunar\Tests\SearchRelevance\TestCase;

uses(TestCase::class)->group('search-relevance');

function trackedResults(array $meta): SearchResults
{
    return SearchResults::from([
        'query' => 'cable',
        'count' => 1,
        'page' => 1,
        'perPage' => 10,
        'totalPages' => 1,
        'hits' => [SearchHit::from(['highlights' => [], 'document' => ['id' => '7'], 'meta' => ['position' => 3, 'source' => 'learned']])],
        'facets' => [],
        'links' => (new LengthAwarePaginator([], 1, 10, 1))->links(),
        'meta' => $meta,
    ]);
}

it('renders the tracking attributes for a logged search', function () {
    $results = trackedResults(['search_id' => '01ARZ3NDEKTSV4RRFFQ69G5FAV']);

    expect((string) lunar_search_attrs($results, $results->hits[0]))
        ->toBe('data-lunar-search-id="01ARZ3NDEKTSV4RRFFQ69G5FAV" data-lunar-product-id="7" data-lunar-position="3" data-lunar-source="learned"');
});

it('renders nothing when the search was not logged', function () {
    $results = trackedResults([]);

    expect((string) lunar_search_attrs($results, $results->hits[0]))->toBe('')
        ->and(trim(Blade::render('<x-lunar-search-relevance::tracking :results="$results" />', ['results' => $results])))->toBe('');
});

it('renders the beacon script once per results page', function () {
    $results = trackedResults(['search_id' => '01ARZ3NDEKTSV4RRFFQ69G5FAV']);

    $html = Blade::render('<x-lunar-search-relevance::tracking :results="$results" />', ['results' => $results]);

    expect($html)->toContain('data-lunar-search-tracking="01ARZ3NDEKTSV4RRFFQ69G5FAV"')
        ->toContain('var endpoint = '.json_encode(route('lunar.search-relevance.events')))
        ->toContain('navigator.sendBeacon')
        ->toContain('keepalive: true')
        ->toContain("closest('[data-lunar-search-id]')")
        ->toContain("'_token'");
});
