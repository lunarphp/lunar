<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Lunar\Core\Models\Product;
use Lunar\Search\Engines\MeilisearchEngine;
use Lunar\Search\Engines\TypesenseEngine;
use Lunar\Search\Facades\Search;
use Lunar\SearchRelevance\Models\SearchQuery;
use Lunar\SearchRelevance\Models\SearchQueryScore;
use Lunar\SearchRelevance\Pipelines\PartNumberFallback;
use Lunar\SearchRelevance\Pipelines\RankResults;
use Lunar\Tests\SearchRelevance\Support\Fixtures;
use Lunar\Tests\SearchRelevance\Support\SupplierCodeIndexer;
use Lunar\Tests\SearchRelevance\TestCase;
use Mockery\MockInterface;

use function Pest\Laravel\partialMock;

uses(TestCase::class)->group('search-relevance');

/**
 * A fake engine whose getRawResults() answers with each item set in turn, so
 * the learned-union fetch (the second call) can return different documents.
 */
function fakeEngine(string $class, string $driver, array ...$responses): object
{
    $engine = partialMock($class, function (MockInterface $mock) use ($responses) {
        $paginators = array_map(fn ($items) => new LengthAwarePaginator(
            items: $items,
            total: count($items['hits']),
            perPage: 50,
            currentPage: 1,
        ), $responses);

        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRawResults')
            ->andReturn(...$paginators);
    });

    Search::extend($driver, fn () => $engine);

    return $engine;
}

function typesenseHits(array $ids): array
{
    return ['hits' => array_map(fn ($id) => ['document' => ['id' => (string) $id], 'text_match' => 100 - $id], $ids), 'facet_counts' => []];
}

function ids($results): array
{
    return collect($results->hits)->map(fn ($hit) => (int) $hit->document['id'])->all();
}

function learn(int $productId, float $relative = 1.0, string $query = 'cable', string $version = 'n1:database:keyword'): void
{
    SearchQueryScore::factory()->create([
        'normalised_query' => $query,
        'product_id' => $productId,
        'relative' => $relative,
        'score' => $relative * 10,
        'version' => $version,
    ]);
}

beforeEach(function () {
    Fixtures::storefront();
    Fixtures::databaseEngine();
    Config::set('lunar.search_relevance.mode', 'shadow');
    Config::set('lunar.search_relevance.window', 250);
    Config::set('lunar.search_relevance.bucket_size', 10);
});

it('widens the request to the window and slices back to the requested page', function () {
    $products = Fixtures::products(30);
    $expected = collect($products)->pluck('id')->slice(10, 10)->values()->all();

    $results = Search::model(Product::class)->query('cable')->perPage(10)->page(2)->get();

    expect($results->count)->toBe(30)
        ->and($results->page)->toBe(2)
        ->and($results->perPage)->toBe(10)
        ->and($results->totalPages)->toBe(3)
        ->and(ids($results))->toBe($expected)
        ->and($results->hits[0]->meta['position'])->toBe(11)
        ->and($results->hits[0]->meta['original_position'])->toBe(11)
        ->and($results->hits[0]->meta['source'])->toBe('organic')
        ->and($results->meta['ranking_mode'])->toBe('shadow')
        ->and($results->meta['ranking_version'])->toBe('n1:database:keyword')
        ->and($results->meta['search_id'])->toHaveLength(26)
        ->and($results->toArray()['links'])->not->toBeEmpty();

    $logged = SearchQuery::query()->find($results->meta['search_id']);

    expect($logged)->not->toBeNull()
        ->and($logged->raw_query)->toBe('cable')
        ->and($logged->normalised_query)->toBe('cable')
        ->and($logged->result_count)->toBe(30)
        ->and($logged->shown)->toBe(collect($products)->pluck('id')->all())
        ->and($logged->ranked)->toBeNull()
        ->and($logged->session_id)->toStartWith('session:')
        ->and($logged->mode)->toBe('shadow');
});

it('leaves a page beyond the window alone but still logs it', function () {
    Config::set('lunar.search_relevance.window', 20);
    $products = Fixtures::products(30);

    $results = Search::model(Product::class)->query('cable')->perPage(10)->page(3)->get();

    expect($results->page)->toBe(3)
        ->and(ids($results))->toBe(collect($products)->pluck('id')->slice(20, 10)->values()->all())
        ->and($results->hits[0]->meta['position'])->toBe(21)
        ->and($results->meta['search_id'])->toHaveLength(26);

    $logged = SearchQuery::query()->find($results->meta['search_id']);

    expect($logged->shown)->toBe(collect($products)->pluck('id')->slice(20, 10)->values()->all())
        ->and($logged->ranked)->toBeNull();
});

it('caps the logged impressions', function () {
    Config::set('lunar.search_relevance.impressions_logged', 5);
    Fixtures::products(8);

    $results = Search::model(Product::class)->query('cable')->perPage(4)->get();

    expect(SearchQuery::query()->find($results->meta['search_id'])->shown)->toHaveCount(5);
});

it('displays the ranked order in on mode', function () {
    Config::set('lunar.search_relevance.mode', 'on');
    $products = Fixtures::products(12);
    learn($products[4]->id);

    $results = Search::model(Product::class)->query('cable')->perPage(10)->get();

    $ids = collect($products)->pluck('id')->all();
    $expected = [$ids[4], $ids[0], $ids[1], $ids[2], $ids[3], $ids[5], $ids[6], $ids[7], $ids[8], $ids[9]];

    expect(ids($results))->toBe($expected)
        ->and($results->hits[0]->meta)->toMatchArray(['position' => 1, 'original_position' => 5, 'boost' => 1.0, 'source' => 'organic'])
        ->and($results->meta['ranking_mode'])->toBe('on');

    $logged = SearchQuery::query()->find($results->meta['search_id']);

    expect($logged->shown)->toBe([...$expected, $ids[10], $ids[11]])
        ->and($logged->ranked)->toBeNull();
});

it('displays the engine order in shadow mode but logs the ranked order', function () {
    $products = Fixtures::products(12);
    learn($products[4]->id);

    $results = Search::model(Product::class)->query('cable')->perPage(10)->get();

    $ids = collect($products)->pluck('id')->all();

    expect(ids($results))->toBe(array_slice($ids, 0, 10))
        ->and($results->hits[4]->meta)->toMatchArray(['position' => 5, 'original_position' => 5, 'boost' => 1.0]);

    $logged = SearchQuery::query()->find($results->meta['search_id']);

    expect($logged->shown)->toBe($ids)
        ->and($logged->ranked)->toBe([$ids[4], $ids[0], $ids[1], $ids[2], $ids[3], $ids[5], $ids[6], $ids[7], $ids[8], $ids[9], $ids[10], $ids[11]])
        ->and($logged->features)->toHaveCount(12)
        ->and($logged->features[0])->toMatchArray(['id' => $ids[4], 'pos' => 1, 'orig' => 5, 'boost' => 1.0, 'src' => 'organic']);
});

it('logs nothing and stamps nothing in off mode', function () {
    Config::set('lunar.search_relevance.mode', 'off');
    Fixtures::products(3);

    $results = Search::model(Product::class)->query('cable')->perPage(2)->get();

    expect($results->meta)->toBe([])
        ->and($results->hits[0]->meta)->toBe([])
        ->and($results->hits)->toHaveCount(2)
        ->and(SearchQuery::query()->count())->toBe(0);
});

it('ignores models that are not configured for ranking', function () {
    Config::set('lunar.search_relevance.models', []);
    Fixtures::products(3);

    $results = Search::model(Product::class)->query('cable')->get();

    expect($results->meta)->toBe([])
        ->and(SearchQuery::query()->count())->toBe(0);
});

it('does not rank a sorted search but still logs it', function () {
    $products = Fixtures::products(12);
    learn($products[4]->id);

    $results = Search::model(Product::class)->query('cable')->sort('created_at:asc')->perPage(10)->get();

    expect(ids($results))->toBe(collect($products)->pluck('id')->slice(0, 10)->values()->all())
        ->and($results->meta['search_id'])->toHaveLength(26)
        ->and(SearchQuery::query()->find($results->meta['search_id'])->ranked)->toBeNull();
});

it('unions learned products the engine missed at the head of the second bucket', function () {
    Config::set('lunar.search_relevance.mode', 'on');
    Config::set('lunar.search_relevance.bucket_size', 3);
    $cables = Fixtures::products(5);
    $hammer = Fixtures::products(1, 'hammer')[0];
    $weak = Fixtures::products(1, 'spanner')[0];
    learn($hammer->id, 0.8);
    learn($weak->id, 0.05);

    $results = Search::model(Product::class)->query('cable')->perPage(10)->get();

    $ids = collect($cables)->pluck('id')->all();

    expect(ids($results))->toBe([$ids[0], $ids[1], $ids[2], $hammer->id, $ids[3], $ids[4]])
        ->and($results->count)->toBe(6)
        ->and($results->hits[3]->meta)->toMatchArray(['position' => 4, 'original_position' => 4, 'boost' => 0.8, 'source' => 'learned'])
        ->and(collect($results->hits[3]->document)->contains('hammer 1'))->toBeTrue();
});

it('keeps the engine order in shadow mode even when a learned product is unioned', function () {
    Config::set('lunar.search_relevance.bucket_size', 3);
    $cables = Fixtures::products(5);
    $hammer = Fixtures::products(1, 'hammer')[0];
    learn($hammer->id);

    $results = Search::model(Product::class)->query('cable')->perPage(10)->get();

    $ids = collect($cables)->pluck('id')->all();

    expect(ids($results))->toBe($ids)
        ->and(SearchQuery::query()->find($results->meta['search_id'])->ranked)->toBe([$ids[0], $ids[1], $ids[2], $hammer->id, $ids[3], $ids[4]]);
});

it('serves the ranked window from the cache on repeat searches', function () {
    Config::set('lunar.search_relevance.mode', 'on');
    $products = Fixtures::products(4);

    $first = Search::model(Product::class)->query('cable')->perPage(10)->get();
    learn($products[2]->id);
    $second = Search::model(Product::class)->query('cable')->perPage(10)->get();

    expect(ids($second))->toBe(ids($first))
        ->and($second->meta['search_id'])->not->toBe($first->meta['search_id'])
        ->and(SearchQuery::query()->count())->toBe(2);
});

it('does not widen or rank a part-number search on the database engine', function () {
    $products = Fixtures::products(3, 'ab12');

    $results = Search::model(Product::class)->query('ab12')->perPage(2)->get();

    expect($results->hits)->toHaveCount(2)
        ->and($results->totalPages)->toBe(2)
        ->and($results->meta['search_id'])->toHaveLength(26)
        ->and(SearchQuery::query()->find($results->meta['search_id']))->toMatchArray([
            'normalised_query' => 'ab12',
            'ranked' => null,
            'shown' => [$products[0]->id, $products[1]->id],
        ]);
});

it('restricts a part-number search to the sku fields on typesense', function () {
    Config::set('scout.driver', 'typesense');
    Config::set('lunar.search.engine_map', [Product::class => 'typesense']);

    $engine = fakeEngine(TypesenseEngine::class, 'typesense', typesenseHits([1, 2]));

    $results = Search::model(Product::class)->query('HAG-MB-32A')->get();

    expect($engine->getParams())->toBe([
        'query_by' => 'skus,skus_normalised',
        'query_by_weights' => null,
        'prefix' => true,
        'infix' => 'always,always',
        'num_typos' => '0,0',
        'drop_tokens_threshold' => 0,
        'vector_query' => null,
    ])
        ->and($engine->getPerPage())->toBe(50)
        ->and($results->meta['ranking_version'])->toBe('n1:typesense:keyword')
        ->and(SearchQuery::query()->find($results->meta['search_id'])->ranked)->toBeNull();
});

it('restricts a part-number search to the sku fields on meilisearch', function () {
    Config::set('scout.driver', 'meilisearch');
    Config::set('lunar.search.engine_map', [Product::class => 'meilisearch']);

    $engine = fakeEngine(MeilisearchEngine::class, 'meilisearch', [
        'hits' => [['id' => '1', '_rankingScore' => 0.9]],
        'query' => 'hagmb32',
        'facetDistribution' => [],
    ]);

    Search::model(Product::class)->query('hagmb32')->get();

    expect($engine->getParams())->toBe([
        'attributesToSearchOn' => ['skus', 'skus_normalised'],
        'matchingStrategy' => 'all',
    ]);
});

it('turns semantic search off for part numbers on meilisearch when an embedder is configured', function () {
    Config::set('scout.driver', 'meilisearch');
    Config::set('lunar.search.engine_map', [Product::class => 'meilisearch']);
    Config::set('lunar.search.meilisearch.embedder', 'default');

    $engine = fakeEngine(MeilisearchEngine::class, 'meilisearch', [
        'hits' => [],
        'query' => 'hagmb32',
        'facetDistribution' => [],
    ]);

    Search::model(Product::class)->query('hagmb32')->get();

    expect($engine->getParams()['hybrid'])->toBe(['semanticRatio' => 0]);
});

it('widens, ranks and unions through a typesense engine', function () {
    Config::set('scout.driver', 'typesense');
    Config::set('lunar.search.engine_map', [Product::class => 'typesense']);
    Config::set('lunar.search_relevance.mode', 'on');
    Config::set('lunar.search_relevance.bucket_size', 3);
    Config::set('lunar.search_relevance.window', 100);
    learn(9, 1.0, version: 'n1:typesense:keyword');
    learn(5, 0.5, version: 'n1:typesense:keyword');

    $engine = fakeEngine(TypesenseEngine::class, 'typesense', typesenseHits([1, 2, 3, 4, 5]), typesenseHits([9, 42]));

    $results = Search::model(Product::class)->query('cable')->perPage(2)->page(2)->get();

    expect($engine->getPerPage())->toBe(100)
        ->and($engine->getPage())->toBe(1)
        // Window after union and ranking: [1,2,3] [9,5,4]; page two of two is [3, 9].
        ->and(ids($results))->toBe([3, 9])
        ->and($results->count)->toBe(6)
        ->and($results->totalPages)->toBe(3)
        ->and($results->hits[0]->meta)->toBe(['score' => 97.0, 'position' => 3, 'original_position' => 3, 'boost' => 0.0, 'source' => 'organic'])
        ->and($results->hits[1]->meta)->toMatchArray(['position' => 4, 'original_position' => 4, 'boost' => 1.0, 'source' => 'learned']);

    $logged = SearchQuery::query()->find($results->meta['search_id']);

    expect($logged->shown)->toBe([1, 2, 3, 9, 5, 4])
        ->and($logged->version)->toBe('n1:typesense:keyword');
});

it('restricts a part-number search to the exact-match fields the indexer declares', function () {
    Config::set('scout.driver', 'typesense');
    Config::set('lunar.search.engine_map', [Product::class => 'typesense']);
    Config::set('lunar.search.indexers', [Product::class => SupplierCodeIndexer::class]);

    $engine = fakeEngine(TypesenseEngine::class, 'typesense', typesenseHits([1]));

    Search::model(Product::class)->query('FTP25')->get();

    expect($engine->getParams())->toMatchArray([
        'query_by' => 'skus,skus_normalised,mpns,eans',
        'infix' => 'always,always,always,always',
        'num_typos' => '0,0,0,0',
    ]);
});

it('searches a part number as usual when the indexer declares no exact-match fields', function () {
    Config::set('scout.driver', 'typesense');
    Config::set('lunar.search.engine_map', [Product::class => 'typesense']);
    Config::set('lunar.search.indexers', [Product::class => SupplierCodeIndexer::class]);
    app()->bind(SupplierCodeIndexer::class, fn () => new SupplierCodeIndexer([]));

    $engine = fakeEngine(TypesenseEngine::class, 'typesense', typesenseHits([1, 2]));

    Search::model(Product::class)->query('FTP25')->get();

    expect($engine->getParams())->toBe([])
        ->and($engine->getPerPage())->toBe(250);
});

it('leaves part-number searches alone when relevance is off', function () {
    Config::set('scout.driver', 'typesense');
    Config::set('lunar.search.engine_map', [Product::class => 'typesense']);
    Config::set('lunar.search_relevance.mode', 'off');

    $engine = fakeEngine(TypesenseEngine::class, 'typesense', typesenseHits([1]));

    $results = Search::model(Product::class)->query('HAG-MB-32A')->get();

    expect($engine->getParams())->toBe([])
        ->and($results->meta)->not->toHaveKey('search_id')
        ->and(SearchQuery::query()->count())->toBe(0);
});

it('reruns a part-number search that matches nothing as an ordinary search', function () {
    Config::set('scout.driver', 'typesense');
    Config::set('lunar.search.engine_map', [Product::class => 'typesense']);

    fakeEngine(TypesenseEngine::class, 'typesense', ['hits' => [], 'facet_counts' => []], typesenseHits([7, 8]));

    $results = Search::model(Product::class)->query('FTP25')->perPage(10)->get();

    expect(ids($results))->toBe([7, 8])
        ->and(SearchQuery::query()->count())->toBe(1)
        ->and(SearchQuery::query()->find($results->meta['search_id'])->shown)->toBe([7, 8]);
});

it('keeps the part-number results when the exact-match fields find something', function () {
    Config::set('scout.driver', 'typesense');
    Config::set('lunar.search.engine_map', [Product::class => 'typesense']);

    fakeEngine(TypesenseEngine::class, 'typesense', typesenseHits([3]), typesenseHits([7, 8]));

    $results = Search::model(Product::class)->query('FTP25')->get();

    expect(ids($results))->toBe([3])
        ->and(SearchQuery::query()->count())->toBe(1);
});

it('registers the part-number fallback ahead of the ranking stage', function () {
    $stages = config('lunar.search.pipelines.results');

    expect(array_search(PartNumberFallback::class, $stages, true))
        ->toBeLessThan(array_search(RankResults::class, $stages, true));
});

it('runs a search without either pipeline when asked', function () {
    Fixtures::products(3);

    $results = Search::model(Product::class)->query('cable')->perPage(2)->withoutPipelines()->get();

    expect($results->hits)->toHaveCount(2)
        ->and($results->meta)->not->toHaveKey('search_id')
        ->and(SearchQuery::query()->count())->toBe(0);
});

it('ranks a search sorted by relevance like an unsorted one', function (string $sort, int $perPage) {
    Config::set('scout.driver', 'typesense');
    Config::set('lunar.search.engine_map', [Product::class => 'typesense']);

    $engine = fakeEngine(TypesenseEngine::class, 'typesense', typesenseHits([1, 2, 3]));

    Search::model(Product::class)->query('cable')->sort($sort)->perPage(2)->get();

    expect($engine->getPerPage())->toBe($perPage);
})->with([
    'relevance' => ['relevance:asc', 250],
    'text match' => ['_text_match:desc', 250],
    'a shopper sort' => ['price:asc', 2],
]);
