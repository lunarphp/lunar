<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Lunar\Models\Product;
use Lunar\Search\Data\SearchResults;
use Lunar\Search\Engines\MeilisearchEngine;
use Lunar\Search\Facades\Search;
use Lunar\Tests\Search\TestCase;
use Mockery\MockInterface;

uses(TestCase::class)->group('search');

function mockWithResponse(array $response)
{
    $engine = \Pest\Laravel\partialMock(MeilisearchEngine::class, function (MockInterface $mock) use ($response) {
        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRawResults')
            ->andReturn(
                new LengthAwarePaginator(
                    items: $response,
                    total: 100,
                    perPage: 50,
                    currentPage: 1
                )
            );
        $mock->shouldReceive('setFacets')->andReturnSelf();
        $mock->shouldReceive('perPage')->andReturnSelf();
        $mock->shouldReceive('sort')->andReturnSelf();
        $mock->shouldReceive('extendQuery')->andReturnSelf();
    });

    Search::extend('meilisearch', fn () => $engine);
}

beforeEach(function () {
    Config::set('scout.driver', 'meilisearch');
    Config::set('lunar.search.engine_map.Lunar\Models\Product', 'meilisearch');
});

it('can fetch empty results', function () {
    mockWithResponse([
        'hits' => [],
        'offset' => 0,
        'limit' => 0,
        'estimatedTotalHits' => 0,
        'processingTimeMs' => 0,
        'query' => '',
    ]);

    $results = Search::model(Product::class)->get();

    expect($results)->toBeInstanceOf(SearchResults::class);
});

it('can search complete results', function () {
    mockWithResponse([
        'hits' => [
            [
                'id' => '123',
                'name' => 'Foo Bar',
            ],
        ],
        'facetDistribution' => [
            'brand' => [
                'Nike' => 100,
                'Adidas' => 100,
                'Puma' => 100,
            ],
            'size' => [
                '10' => 100,
                '12' => 50,
            ],
        ],
        'offset' => 0,
        'limit' => 0,
        'estimatedTotalHits' => 0,
        'processingTimeMs' => 0,
        'query' => '',
    ]);

    $results = Search::model(Product::class)->get();

    expect($results->hits)
        ->toHaveCount(1)
        ->and($results->facets)
        ->toHaveCount(2)
        ->and($results->facets[0]->label)
        ->toBe('brand')
        ->and($results->facets[0]->values)
        ->toHaveCount(3)
        ->and($results->facets[0]->values[0]->label)
        ->toBe('Nike');
});
