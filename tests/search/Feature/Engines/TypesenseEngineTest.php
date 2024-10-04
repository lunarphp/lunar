<?php

use Typesense\ApiCall;

uses(\Lunar\Tests\Search\TestCase::class)->group('search');
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Mockery::mock(ApiCall::class);
});

test('can get search results', function () {
    $engine = \Pest\Laravel\partialMock(Lunar\Search\Engines\TypesenseEngine::class, function (\Mockery\MockInterface $mock) {
        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRawResults')
            ->andReturn(
                new \Illuminate\Pagination\LengthAwarePaginator(
                    items: [
                        'hits' => [],
                        'facet_counts' => [],
                        'request_params' => [
                            'q' => 'Hello',
                        ],
                    ],
                    total: 100,
                    perPage: 50,
                    currentPage: 1
                )
            );
        $mock->shouldReceive('query')->andReturnSelf();
    });

    $results = $engine->query('')->get();

    expect($results)
        ->toBeInstanceOf(\Lunar\Search\Data\SearchResults::class)
        ->and($results->count)
        ->toBe(100)
        ->and($results->perPage)
        ->toBe(50)
        ->and($results->totalPages)
        ->toBe(2)
        ->and($results->links)
        ->toBeArray()
        ->and($results->links)
        ->toHaveCount(4);
});

test('can map search results to spatie data objects', function () {
    $engine = \Pest\Laravel\partialMock(Lunar\Search\Engines\TypesenseEngine::class, function (\Mockery\MockInterface $mock) {
        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRawResults')
            ->andReturn(
                new \Illuminate\Pagination\LengthAwarePaginator(
                    items: [
                        'hits' => [
                            [
                                'highlights' => [

                                ],
                                'document' => [
                                    'id' => 123,
                                    'name' => 'Foobar',
                                ]
                            ]
                        ],
                        'facet_counts' => [],
                        'request_params' => [
                            'q' => '',
                        ],
                    ],
                    total: 0,
                    perPage: 50,
                    currentPage: 1
                )
            );
        $mock->shouldReceive('query')->andReturnSelf();
    });

    $results = $engine->query('')->get();

    expect($results)
        ->toBeInstanceOf(\Lunar\Search\Data\SearchResults::class)
        ->and($results->hits[0])
        ->toBeInstanceOf(\Lunar\Search\Data\SearchHit::class)
        ->and($results->hits[0]->document['name'])
        ->toBe('Foobar');
});
