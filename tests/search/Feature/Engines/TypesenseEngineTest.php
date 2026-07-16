<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Lunar\Core\Models\Product;
use Lunar\Search\Data\SearchResults;
use Lunar\Search\Engines\TypesenseEngine;
use Lunar\Search\Facades\Search;
use Lunar\Tests\Search\TestCase;
use Mockery\MockInterface;

use function Pest\Laravel\partialMock;

uses(TestCase::class)->group('search');

function mockTypesenseWithResponse(array $response)
{
    $engine = partialMock(TypesenseEngine::class, function (MockInterface $mock) use ($response) {
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
        $mock->shouldReceive('extendQuery')->andReturnSelf();
    });

    Search::extend('typesense', fn () => $engine);
}

beforeEach(function () {
    Config::set('scout.driver', 'typesense');
    Config::set('lunar.search.engine_map.Lunar\Core\Models\Product', 'typesense');
});

it('can fetch empty results', function () {
    mockTypesenseWithResponse([
        'hits' => [],
        'facet_counts' => [],
    ]);

    $results = Search::model(Product::class)->get();

    expect($results)->toBeInstanceOf(SearchResults::class);
});

it('returns facets as a list even when keyed by field name', function () {
    // getRawResults returns facet_counts keyed by field name — the shape the
    // engine's multi-search closure produces when merging per-facet searches.
    mockTypesenseWithResponse([
        'hits' => [
            [
                'document' => [
                    'id' => '123',
                    'name' => 'Foo Bar',
                ],
            ],
        ],
        'facet_counts' => [
            'brand' => [
                'field_name' => 'brand',
                'counts' => [
                    ['value' => 'Nike', 'count' => 100],
                    ['value' => 'Adidas', 'count' => 100],
                ],
            ],
            'size' => [
                'field_name' => 'size',
                'counts' => [
                    ['value' => '10', 'count' => 100],
                ],
            ],
        ],
    ]);

    $results = Search::model(Product::class)->get();

    expect($results->hits)
        ->toHaveCount(1)
        ->and($results->facets)
        ->toHaveCount(2)
        ->and(array_is_list($results->facets))
        ->toBeTrue()
        ->and($results->facets[0]->field)
        ->toBe('brand')
        ->and($results->facets[0]->values)
        ->toHaveCount(2)
        ->and($results->facets[0]->values[0]->label)
        ->toBe('Nike');
});

it('carries the requested sort and serialises with camelCase keys', function () {
    mockTypesenseWithResponse([
        'hits' => [],
        'facet_counts' => [],
    ]);

    $results = Search::model(Product::class)->sort('min_price:desc')->get();

    expect($results->sortField)
        ->toBe('min_price')
        ->and($results->sortDirection)
        ->toBe('desc');

    $array = $results->toArray();

    expect($array)
        ->toHaveKeys(['perPage', 'totalPages', 'sortField', 'sortDirection'])
        ->and($array)
        ->not->toHaveKeys(['per_page', 'total_pages']);
});

it('returns a null sort when none was requested', function () {
    mockTypesenseWithResponse([
        'hits' => [],
        'facet_counts' => [],
    ]);

    $results = Search::model(Product::class)->get();

    expect($results->sortField)
        ->toBeNull()
        ->and($results->sortDirection)
        ->toBeNull();
});
