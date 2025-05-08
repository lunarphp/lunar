<?php

uses(\Lunar\Tests\Search\TestCase::class)->group('search');

function mockWithResponse(array $response)
{
    $engine = \Pest\Laravel\partialMock(Lunar\Search\Engines\MeilisearchEngine::class, function (\Mockery\MockInterface $mock) use ($response) {
        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRawResults')
            ->andReturn(
                new \Illuminate\Pagination\LengthAwarePaginator(
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

    \Lunar\Search\Facades\Search::extend('meilisearch', fn () => $engine);
}

beforeEach(function () {
    \Illuminate\Support\Facades\Config::set('scout.driver', 'meilisearch');
    \Illuminate\Support\Facades\Config::set('lunar.search.engine_map.Lunar\Models\Product', 'meilisearch');
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

    $results = \Lunar\Search\Facades\Search::model(\Lunar\Models\Product::class)->get();

    expect($results)->toBeInstanceOf(\Lunar\Search\Data\SearchResults::class);
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

    $results = \Lunar\Search\Facades\Search::model(\Lunar\Models\Product::class)->get();

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
