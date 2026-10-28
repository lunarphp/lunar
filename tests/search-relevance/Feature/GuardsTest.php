<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Lunar\Core\Events\Orders\OrderCancelled;
use Lunar\Core\Events\Orders\OrderRefunded;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Search\Engines\TypesenseEngine;
use Lunar\Search\Facades\Search;
use Lunar\SearchRelevance\Listeners\ForgetPurchases;
use Lunar\SearchRelevance\Models\SearchEvent;
use Lunar\SearchRelevance\Models\SearchQuery;
use Lunar\Tests\SearchRelevance\Support\Fixtures;
use Lunar\Tests\SearchRelevance\TestCase;
use Mockery\MockInterface;

use function Pest\Laravel\partialMock;

uses(TestCase::class)->group('search-relevance');

beforeEach(function () {
    Fixtures::storefront();
});

/** Route product searches to a fake Typesense engine that answers with two hits. */
function fakeTypesenseForGuards(): void
{
    Config::set('scout.driver', 'typesense');
    Config::set('lunar.search.engine_map', [Product::class => 'typesense']);

    $engine = partialMock(TypesenseEngine::class, function (MockInterface $mock) {
        $mock->shouldAllowMockingProtectedMethods()->shouldReceive('getRawResults')->andReturn(new LengthAwarePaginator(
            items: ['hits' => [['document' => ['id' => '1']], ['document' => ['id' => '2']]], 'facet_counts' => []],
            total: 2,
            perPage: 50,
            currentPage: 1,
        ));
    });
    Search::extend('typesense', fn () => $engine);
}

it('neither logs nor stamps searches from crawlers', function (string $agent) {
    fakeTypesenseForGuards();
    app('request')->headers->set('User-Agent', $agent);

    $results = Search::model(Product::class)->query('hoodie')->get();

    expect($results->meta)->not->toHaveKey('search_id')
        ->and(SearchQuery::query()->count())->toBe(0);
})->with(['Mozilla/5.0 (compatible; Googlebot/2.1)', 'curl/8.4.0', 'python-requests/2.31']);

it('logs an ordinary browser search, with or without a session', function () {
    fakeTypesenseForGuards();
    app('request')->headers->set('User-Agent', 'Mozilla/5.0 Safari');

    $results = Search::model(Product::class)->query('hoodie')->get();

    expect($results->meta)->toHaveKey('search_id')
        ->and(SearchQuery::query()->count())->toBe(1);
});

it('forgets purchase events when the order is cancelled or refunded', function (string $eventClass) {
    $variant = ProductVariant::factory()->create();
    $search = SearchQuery::factory()->create(['shown' => [$variant->product_id]]);
    SearchEvent::factory()->create(['search_id' => $search->id, 'product_id' => $variant->product_id, 'type' => 'purchase']);
    SearchEvent::factory()->create(['search_id' => $search->id, 'product_id' => $variant->product_id, 'type' => 'click']);

    $order = Order::factory()->create();
    OrderLine::factory()->create([
        'order_id' => $order->id,
        'purchasable_id' => $variant->id,
        'meta' => ['search_attribution' => ['search_id' => $search->id, 'position' => 1, 'source' => 'organic']],
    ]);

    app(ForgetPurchases::class)->handle(new $eventClass($order));

    expect(SearchEvent::query()->pluck('type')->all())->toBe(['click']);
})->with([OrderCancelled::class, OrderRefunded::class]);
