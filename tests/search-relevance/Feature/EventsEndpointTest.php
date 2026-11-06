<?php

use Illuminate\Support\Facades\Config;
use Lunar\SearchRelevance\Models\SearchEvent;
use Lunar\SearchRelevance\Models\SearchQuery;
use Lunar\Tests\SearchRelevance\Support\Fixtures;
use Lunar\Tests\SearchRelevance\TestCase;

use function Pest\Laravel\post;
use function Pest\Laravel\postJson;

uses(TestCase::class)->group('search-relevance');

beforeEach(function () {
    Fixtures::storefront();
    $this->search = SearchQuery::factory()->create(['shown' => [10, 20, 30]]);
});

it('records a click and stores attribution in the session', function () {
    $response = postJson(route('lunar.search-relevance.events'), [
        'search_id' => $this->search->id,
        'product_id' => 20,
        'position' => 2,
        'source' => 'learned',
    ]);

    $response->assertNoContent();

    $event = SearchEvent::query()->sole();

    expect($event)->toMatchArray(['search_id' => $this->search->id, 'product_id' => 20, 'position' => 2, 'type' => 'click', 'source' => 'learned'])
        ->and($event->session_id)->toStartWith('session:')
        ->and($event->created_at)->not->toBeNull();

    $attribution = session()->get('lunar_search_relevance.attribution.20');

    expect($attribution)->toMatchArray(['search_id' => $this->search->id, 'position' => 2, 'source' => 'learned', 'session_id' => $event->session_id])
        ->and($attribution['expires'])->toBeGreaterThan(now()->getTimestamp());
});

it('accepts a form post with an explicit shopper id', function () {
    post(route('lunar.search-relevance.events'), [
        'search_id' => $this->search->id,
        'product_id' => 10,
        'position' => 1,
        'session_id' => 'cart:99',
    ])->assertNoContent();

    expect(SearchEvent::query()->sole())->toMatchArray(['product_id' => 10, 'source' => 'organic', 'session_id' => 'cart:99']);
});

it('answers 204 without recording anything for bad input', function (array $payload) {
    postJson(route('lunar.search-relevance.events'), $payload)->assertNoContent();

    expect(SearchEvent::query()->count())->toBe(0)
        ->and(session()->get('lunar_search_relevance.attribution'))->toBeNull();
})->with([
    'unknown search' => fn () => ['search_id' => '01ARZ3NDEKTSV4RRFFQ69G5FAV', 'product_id' => 10, 'position' => 1],
    'malformed search id' => fn () => ['search_id' => 'nope', 'product_id' => 10, 'position' => 1],
    'missing product' => fn () => ['search_id' => $this->search->id, 'position' => 1],
    'position below one' => fn () => ['search_id' => $this->search->id, 'product_id' => 10, 'position' => 0],
    'unknown source' => fn () => ['search_id' => $this->search->id, 'product_id' => 10, 'position' => 1, 'source' => 'paid'],
]);

it('drops events for products the search did not show or positions out of range', function () {
    postJson(route('lunar.search-relevance.events'), ['search_id' => $this->search->id, 'product_id' => 99, 'position' => 1])->assertNoContent();
    postJson(route('lunar.search-relevance.events'), ['search_id' => $this->search->id, 'product_id' => 10, 'position' => 4])->assertNoContent();

    expect(SearchEvent::query()->count())->toBe(0);
});

it('rate limits per shopper', function () {
    Config::set('lunar.search_relevance.guards.events_rate_limit', '2,1');

    $payload = ['search_id' => $this->search->id, 'product_id' => 10, 'position' => 1];

    postJson(route('lunar.search-relevance.events'), $payload)->assertNoContent();
    postJson(route('lunar.search-relevance.events'), $payload)->assertNoContent();
    postJson(route('lunar.search-relevance.events'), $payload)->assertStatus(429);
});

it('records each event type once per search and product', function () {
    $search = SearchQuery::factory()->create(['shown' => [7, 8]]);
    $payload = ['search_id' => $search->id, 'product_id' => 7, 'position' => 1];

    $this->postJson(route('lunar.search-relevance.events'), $payload)->assertNoContent();
    $this->postJson(route('lunar.search-relevance.events'), [...$payload, 'position' => 2])->assertNoContent();

    expect(SearchEvent::query()->count())->toBe(1)
        ->and(SearchEvent::query()->first()->position)->toBe(1);
});

it('drops events for searches older than the event window', function () {
    Config::set('lunar.search_relevance.guards.event_window_minutes', 60);
    $search = SearchQuery::factory()->create(['shown' => [7], 'created_at' => now()->subMinutes(61)]);

    $this->postJson(route('lunar.search-relevance.events'), ['search_id' => $search->id, 'product_id' => 7, 'position' => 1])->assertNoContent();

    expect(SearchEvent::query()->count())->toBe(0)
        ->and(session()->has('lunar_search_relevance.attribution.7'))->toBeFalse();
});
