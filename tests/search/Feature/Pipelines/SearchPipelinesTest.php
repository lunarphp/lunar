<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Search\Data\SearchResults;
use Lunar\Search\Engines\AbstractEngine;
use Lunar\Search\Engines\MeilisearchEngine;
use Lunar\Search\Engines\TypesenseEngine;
use Lunar\Search\Facades\Search;
use Lunar\Search\Pipelines\SearchRequest;
use Lunar\Search\Pipelines\SearchResponse;
use Lunar\Tests\Search\TestCase;
use Mockery\MockInterface;

use function Pest\Laravel\partialMock;

uses(TestCase::class)->group('search');
uses(RefreshDatabase::class);

/** Records the order stages ran in and what each saw. */
final class PipelineSpy
{
    /** @var array<int, string> */
    public static array $log = [];

    public static ?SearchRequest $request = null;

    public static function reset(): void
    {
        self::$log = [];
        self::$request = null;
    }
}

final class FirstRequestStage
{
    public function handle(SearchRequest $request, Closure $next): SearchRequest
    {
        PipelineSpy::$log[] = 'first';
        $request->context['requested'] = [$request->requestedPage, $request->requestedPerPage];
        PipelineSpy::$request = $request;

        return $next($request);
    }
}

final class WideningRequestStage
{
    public function handle(SearchRequest $request, Closure $next): SearchRequest
    {
        PipelineSpy::$log[] = 'widen';
        $request->engine->page(1)->perPage(100);

        return $next($request);
    }
}

final class ReverseResultsStage
{
    public function handle(SearchResponse $response, Closure $next): SearchResponse
    {
        PipelineSpy::$log[] = 'reverse';

        $results = $response->results;
        $response->results = SearchResults::from([
            ...$results->toArray(),
            'hits' => array_reverse($results->hits),
            'facets' => $results->facets,
            'links' => $results->links,
            'meta' => ['reversed' => true, 'context' => $response->request->context],
        ]);

        return $next($response);
    }
}

function fakeEngineWithHits(string $class, string $driver, array $items): void
{
    $engine = partialMock($class, function (MockInterface $mock) use ($items) {
        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRawResults')
            ->andReturnUsing(fn () => new LengthAwarePaginator(
                items: $items,
                total: count($items['hits']),
                perPage: 50,
                currentPage: 1
            ));
    });

    Search::extend($driver, fn () => $engine);
}

beforeEach(function () {
    PipelineSpy::reset();
    Language::factory()->create(['default' => true, 'code' => 'en']);
});

it('runs request and results stages in config order for the database engine', function () {
    Config::set('scout.driver', 'database');
    Config::set('lunar.search.engine_map', [Product::class => 'database']);
    Config::set('lunar.search.pipelines.request', [FirstRequestStage::class, WideningRequestStage::class]);
    Config::set('lunar.search.pipelines.results', [ReverseResultsStage::class]);

    Product::factory()->count(3)->create();

    $results = Search::model(Product::class)->perPage(2)->page(2)->get();

    expect(PipelineSpy::$log)->toBe(['first', 'widen', 'reverse'])
        ->and(PipelineSpy::$request->context['requested'])->toBe([2, 2])
        // The widening stage replaced the requested page with the full window.
        ->and($results->hits)->toHaveCount(3)
        ->and($results->page)->toBe(1)
        ->and($results->meta['reversed'])->toBeTrue()
        ->and($results->meta['context']['requested'])->toBe([2, 2]);
});

it('honours page() when paginating the database engine', function () {
    Config::set('scout.driver', 'database');
    Config::set('lunar.search.engine_map', [Product::class => 'database']);

    Product::factory()->count(3)->create();

    $results = Search::model(Product::class)->perPage(2)->page(2)->get();

    expect($results->page)->toBe(2)
        ->and($results->hits)->toHaveCount(1)
        ->and($results->totalPages)->toBe(2);
});

it('runs the pipelines for the typesense engine and lets a stage reorder hits', function () {
    Config::set('scout.driver', 'typesense');
    Config::set('lunar.search.engine_map', [Product::class => 'typesense']);
    Config::set('lunar.search.pipelines.request', [FirstRequestStage::class]);
    Config::set('lunar.search.pipelines.results', [ReverseResultsStage::class]);

    fakeEngineWithHits(TypesenseEngine::class, 'typesense', [
        'hits' => [
            ['document' => ['id' => '1']],
            ['document' => ['id' => '2']],
        ],
        'facet_counts' => [],
    ]);

    $results = Search::model(Product::class)->get();

    expect(PipelineSpy::$log)->toBe(['first', 'reverse'])
        ->and(collect($results->hits)->map(fn ($hit) => $hit->document['id'])->all())->toBe(['2', '1'])
        ->and($results->meta['reversed'])->toBeTrue();
});

it('runs the pipelines for the meilisearch engine and lets a stage reorder hits', function () {
    Config::set('scout.driver', 'meilisearch');
    Config::set('lunar.search.engine_map', [Product::class => 'meilisearch']);
    Config::set('lunar.search.pipelines.request', [FirstRequestStage::class]);
    Config::set('lunar.search.pipelines.results', [ReverseResultsStage::class]);

    fakeEngineWithHits(MeilisearchEngine::class, 'meilisearch', [
        'hits' => [
            ['id' => '1', '_rankingScore' => 0.9],
            ['id' => '2', '_rankingScore' => 0.4],
        ],
        'query' => 'foo',
        'facetDistribution' => [],
    ]);

    $results = Search::model(Product::class)->query('foo')->get();

    expect(PipelineSpy::$log)->toBe(['first', 'reverse'])
        ->and(collect($results->hits)->map(fn ($hit) => $hit->document['id'])->all())->toBe(['2', '1'])
        // The ranking score moves to meta rather than polluting the document.
        ->and($results->hits[1]->meta)->toBe(['score' => 0.9])
        ->and($results->hits[1]->document)->not->toHaveKey('_rankingScore');
});

it('defaults meta to an empty array on hits and results', function () {
    Config::set('scout.driver', 'database');
    Config::set('lunar.search.engine_map', [Product::class => 'database']);

    Product::factory()->create();

    $results = Search::model(Product::class)->get();

    expect($results->meta)->toBe([])
        ->and($results->hits[0]->meta)->toBe([])
        ->and($results->toArray())->toHaveKey('meta');
});

it('tracks page and params on the engine', function () {
    $engine = new class extends AbstractEngine
    {
        public function get(): mixed
        {
            return null;
        }

        protected function getFieldConfig(): array
        {
            return [];
        }
    };

    expect($engine->getPage())->toBe(1)
        ->and($engine->page(0)->getPage())->toBe(1)
        ->and($engine->page(3)->getPage())->toBe(3)
        ->and($engine->perPage(24)->getPerPage())->toBe(24)
        ->and($engine->withParams(['a' => 1])->withParams(['b' => 2, 'a' => 3])->getParams())->toBe(['a' => 3, 'b' => 2]);
});
