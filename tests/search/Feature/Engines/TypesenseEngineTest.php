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

/**
 * Like mockTypesenseWithResponse but leaves setFacets() real, so tests can
 * exercise behaviour that depends on the applied facet filters.
 */
function mockTypesenseRawResults(array $response)
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
    });

    Search::extend('typesense', fn () => $engine);
}

/**
 * A TypesenseEngine exposing its protected request-building internals.
 */
function typesenseTestEngine(): TypesenseEngine
{
    return new class extends TypesenseEngine
    {
        public function exposedBuildSearch(array $options): array
        {
            return $this->buildSearch($options);
        }

        public function exposedStripListEntry(mixed $list, int $index): mixed
        {
            return $this->stripListEntry($list, $index);
        }
    };
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

it('maps array-field highlights to one highlight per snippet', function () {
    // string[] fields are highlighted per element: a `snippets` list with
    // index-aligned nested `matched_tokens`.
    mockTypesenseWithResponse([
        'hits' => [
            [
                'document' => ['id' => '1', 'name' => 'Red Shoe'],
                'highlights' => [
                    [
                        'field' => 'sizes',
                        'snippets' => ['<mark>10</mark>', '11'],
                        'matched_tokens' => [['10'], []],
                    ],
                ],
            ],
        ],
        'facet_counts' => [],
    ]);

    $results = Search::model(Product::class)->get();
    $highlights = $results->hits[0]->highlights;

    expect($highlights)
        ->toHaveCount(2)
        ->and($highlights[0]->field)
        ->toBe('sizes')
        ->and($highlights[0]->matches)
        ->toBe(['10'])
        ->and($highlights[0]->snippet)
        ->toBe('<mark>10</mark>')
        ->and($highlights[1]->matches)
        ->toBe([])
        ->and($highlights[1]->snippet)
        ->toBe('11');
});

it('maps scalar-field highlights and tolerates missing keys', function () {
    mockTypesenseWithResponse([
        'hits' => [
            [
                'document' => ['id' => '1', 'name' => 'Red Shoe'],
                'highlights' => [
                    [
                        'field' => 'name',
                        'snippet' => '<mark>Red</mark> Shoe',
                        'matched_tokens' => ['Red'],
                    ],
                    [
                        // Typesense can omit snippet/matched_tokens entirely.
                        'field' => 'description',
                    ],
                ],
            ],
        ],
        'facet_counts' => [],
    ]);

    $results = Search::model(Product::class)->get();
    $highlights = $results->hits[0]->highlights;

    expect($highlights)
        ->toHaveCount(2)
        ->and($highlights[0]->matches)
        ->toBe(['Red'])
        ->and($highlights[0]->snippet)
        ->toBe('<mark>Red</mark> Shoe')
        ->and($highlights[1]->matches)
        ->toBe([])
        ->and($highlights[1]->snippet)
        ->toBeNull();
});

it('marks facet values matching the applied filters as active', function () {
    mockTypesenseRawResults([
        'hits' => [],
        'facet_counts' => [
            'brand' => [
                'field_name' => 'brand',
                'counts' => [
                    ['value' => 'Nike', 'count' => 10],
                    ['value' => 'Adidas', 'count' => 5],
                ],
            ],
        ],
    ]);

    $results = Search::model(Product::class)
        ->setFacets(['brand' => ['Nike']])
        ->get();

    expect($results->facets[0]->values[0]->active)
        ->toBeTrue()
        ->and($results->facets[0]->values[1]->active)
        ->toBeFalse();
});

it('falls back to a match-all query and strips embedding fields when browsing', function () {
    $params = typesenseTestEngine()->exposedBuildSearch([
        'query_by' => 'name, embedding, description',
        'query_by_weights' => '4, 1, 2',
        'infix' => 'off, off, fallback',
        'filter_by' => [],
    ])[0];

    expect($params['q'])
        ->toBe('*')
        ->and($params['query_by'])
        ->toBe('name,description')
        ->and($params['query_by_weights'])
        ->toBe('4,2')
        ->and($params['infix'])
        ->toBe('off,fallback')
        ->and($params)
        ->not->toHaveKey('vector_query');
});

it('passes query_by, weights and infix through untouched when a term is searched', function () {
    $params = typesenseTestEngine()
        ->query('shoes')
        ->exposedBuildSearch([
            'query_by' => 'name, embedding, description',
            'query_by_weights' => '4, 1, 2',
            'infix' => 'off, off, fallback',
            'filter_by' => [],
        ])[0];

    expect($params['q'])
        ->toBe('shoes')
        ->and($params['query_by'])
        ->toBe('name, embedding, description')
        ->and($params['query_by_weights'])
        ->toBe('4, 1, 2')
        ->and($params['infix'])
        ->toBe('off, off, fallback');
});

it('omits query_by_weights and infix when scout does not provide them', function () {
    $params = typesenseTestEngine()->exposedBuildSearch([
        'query_by' => 'name, description',
        'filter_by' => [],
    ])[0];

    expect($params)
        ->not->toHaveKey('query_by_weights')
        ->and($params)
        ->not->toHaveKey('infix');
});

it('excludes the embedding field from hit payloads', function () {
    $params = typesenseTestEngine()->exposedBuildSearch([
        'query_by' => 'name',
        'filter_by' => [],
    ])[0];

    expect($params['exclude_fields'])
        ->toBe('embedding')
        ->and($params)
        ->not->toHaveKey('exlude_fields');
});

it('requests max facet values from the builder with a default of 50', function () {
    $options = ['query_by' => 'name', 'filter_by' => []];

    expect(typesenseTestEngine()->exposedBuildSearch($options)[0]['max_facet_values'])
        ->toBe(50);

    expect(typesenseTestEngine()->maxFacetValues(200)->exposedBuildSearch($options)[0]['max_facet_values'])
        ->toBe(200);
});

it('requests a vector query only when searching a schema with an embedding field', function () {
    $options = [
        'query_by' => 'name, embedding',
        'filter_by' => [],
    ];

    Config::set(
        'scout.typesense.model-settings.'.Product::class.'.collection-schema.fields',
        [['name' => 'name', 'type' => 'string'], ['name' => 'embedding', 'type' => 'float[]']]
    );

    expect(typesenseTestEngine()->query('shoes')->exposedBuildSearch($options)[0]['vector_query'])
        ->toBe('embedding:([], k: 200)');

    // No term to embed — browse requests never carry a vector query.
    expect(typesenseTestEngine()->exposedBuildSearch($options)[0])
        ->not->toHaveKey('vector_query');

    Config::set('scout.typesense.model-settings.'.Product::class.'.collection-schema.fields', [
        ['name' => 'name', 'type' => 'string'],
    ]);

    expect(typesenseTestEngine()->query('shoes')->exposedBuildSearch($options)[0])
        ->not->toHaveKey('vector_query');
});

it('strips a single entry from position-aligned parameter lists', function () {
    $engine = typesenseTestEngine();

    expect($engine->exposedStripListEntry('4, 1, 2', 1))
        ->toBe('4,2')
        ->and($engine->exposedStripListEntry('4', 0))
        ->toBe('')
        ->and($engine->exposedStripListEntry(null, 1))
        ->toBeNull()
        ->and($engine->exposedStripListEntry(['not', 'a', 'string'], 1))
        ->toBe(['not', 'a', 'string']);
});

it('merges the model search-parameters over the options scout supplies', function () {
    Config::set('scout.typesense.model-settings.'.Product::class.'.search-parameters', [
        'query_by_weights' => '10,5',
        'infix' => 'always,off',
        'prioritize_token_position' => 'true',
        'num_typos' => '1',
    ]);

    // Scout only forwards query_by and prefix, so everything else arrives from
    // the model settings or not at all.
    $params = typesenseTestEngine()->query('shoes')->exposedBuildSearch([
        'query_by' => 'name,description',
        'filter_by' => [],
    ])[0];

    expect($params['query_by_weights'])
        ->toBe('10,5')
        ->and($params['infix'])
        ->toBe('always,off')
        ->and($params['prioritize_token_position'])
        ->toBe('true')
        ->and($params['num_typos'])
        ->toBe('1');
});

it('takes prefix from the model search-parameters, defaulting to false', function () {
    $options = ['query_by' => 'name,description', 'filter_by' => []];

    expect(typesenseTestEngine()->exposedBuildSearch($options)[0]['prefix'])
        ->toBeFalse();

    Config::set(
        'scout.typesense.model-settings.'.Product::class.'.search-parameters',
        ['prefix' => 'true,false']
    );

    expect(typesenseTestEngine()->exposedBuildSearch($options)[0]['prefix'])
        ->toBe('true,false');
});

it('unions the host exclude_fields with the embedding field', function () {
    Config::set(
        'scout.typesense.model-settings.'.Product::class.'.search-parameters',
        ['exclude_fields' => 'notes, embedding, internal_ref']
    );

    $params = typesenseTestEngine()->exposedBuildSearch([
        'query_by' => 'name',
        'filter_by' => [],
    ])[0];

    expect($params['exclude_fields'])
        ->toBe('notes,embedding,internal_ref');
});

it('strips the embedding entry from the prefix list when browsing', function () {
    Config::set(
        'scout.typesense.model-settings.'.Product::class.'.search-parameters',
        ['prefix' => 'false,true,true']
    );

    // Without a term the embedding field leaves query_by, so every
    // position-aligned list has to lose the same index or Typesense rejects
    // the request over the count mismatch.
    $params = typesenseTestEngine()->exposedBuildSearch([
        'query_by' => 'embedding,name,description',
        'filter_by' => [],
    ])[0];

    expect($params['query_by'])
        ->toBe('name,description')
        ->and($params['prefix'])
        ->toBe('true,true');
});

it('leaves numeric facet filter values unquoted', function () {
    Config::set('scout.typesense.model-settings.'.Product::class.'.collection-schema.fields', [
        ['name' => 'collection_ids', 'type' => 'int64[]'],
        ['name' => 'stock_level', 'type' => 'int32'],
        ['name' => 'rating', 'type' => 'float'],
    ]);

    // Backticks escape a string literal; on a numeric field Typesense reads
    // them as a comparator and rejects the whole request.
    $filters = typesenseTestEngine()
        ->setFacets([
            'collection_ids' => [535, 533],
            'stock_level' => [4],
            'rating' => ['4.5'],
        ])
        ->exposedBuildSearch(['query_by' => 'name', 'filter_by' => []])[0]['filter_by'];

    expect($filters)
        ->toContain('collection_ids:[535,533]')
        ->and($filters)
        ->toContain('stock_level:=4')
        ->and($filters)
        ->toContain('rating:=4.5')
        ->and($filters)
        ->not->toContain('`');
});

it('quotes string facet filter values, including numeric-looking ones', function () {
    Config::set('scout.typesense.model-settings.'.Product::class.'.collection-schema.fields', [
        ['name' => 'brand', 'type' => 'string'],
        ['name' => 'sizes', 'type' => 'string[]'],
    ]);

    $filters = typesenseTestEngine()
        ->setFacets([
            'brand' => ['3M'],
            'sizes' => ['10', 'Extra Large'],
        ])
        ->exposedBuildSearch(['query_by' => 'name', 'filter_by' => []])[0]['filter_by'];

    expect($filters)
        ->toContain('brand:=`3M`')
        ->and($filters)
        ->toContain('sizes:[`10`,`Extra Large`]');
});

it('normalises bool facet filter values', function () {
    Config::set('scout.typesense.model-settings.'.Product::class.'.collection-schema.fields', [
        ['name' => 'oversized', 'type' => 'bool'],
        ['name' => 'clearance', 'type' => 'bool'],
    ]);

    $filters = typesenseTestEngine()
        ->setFacets([
            'oversized' => [true],
            'clearance' => ['false'],
        ])
        ->exposedBuildSearch(['query_by' => 'name', 'filter_by' => []])[0]['filter_by'];

    expect($filters)
        ->toContain('oversized:=true')
        ->and($filters)
        ->toContain('clearance:=false');
});

it('quotes facet filter values for fields the schema does not declare', function () {
    Config::set('scout.typesense.model-settings.'.Product::class.'.collection-schema.fields', [
        ['name' => 'name', 'type' => 'string'],
    ]);

    // An unmapped field keeps the quoted form it has always had, so a host
    // whose schema lives outside this config cannot regress.
    $filters = typesenseTestEngine()
        ->setFacets(['colour' => ['Red']])
        ->exposedBuildSearch(['query_by' => 'name', 'filter_by' => []])[0]['filter_by'];

    expect($filters)
        ->toContain('colour:=`Red`');
});
