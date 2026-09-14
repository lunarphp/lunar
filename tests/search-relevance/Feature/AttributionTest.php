<?php

use Lunar\Core\Events\Orders\OrderPlaced;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\ProductVariant;
use Lunar\SearchRelevance\Models\SearchEvent;
use Lunar\SearchRelevance\Models\SearchQuery;
use Lunar\SearchRelevance\Support\Attribution;
use Lunar\Tests\SearchRelevance\Support\Fixtures;
use Lunar\Tests\SearchRelevance\TestCase;

uses(TestCase::class)->group('search-relevance');

beforeEach(function () {
    Fixtures::storefront();
    $this->variant = ProductVariant::factory()->create();
    $this->search = SearchQuery::factory()->create(['shown' => [$this->variant->product_id, 999]]);
});

it('credits a new cart line to the clicked search and records a basket event', function () {
    app(Attribution::class)->remember($this->variant->product_id, $this->search->id, 1, 'organic', 'cart:7');

    $line = CartLine::factory()->create(['purchasable_id' => $this->variant->id]);

    expect($line->fresh()->meta['search_attribution'])->toBe([
        'search_id' => $this->search->id,
        'position' => 1,
        'source' => 'organic',
        'session_id' => 'cart:7',
    ]);

    expect(SearchEvent::query()->sole())->toMatchArray([
        'search_id' => $this->search->id,
        'product_id' => $this->variant->product_id,
        'position' => 1,
        'type' => 'basket',
        'session_id' => 'cart:7',
    ]);
});

it('leaves a cart line alone without attribution', function () {
    $line = CartLine::factory()->create(['purchasable_id' => $this->variant->id]);

    expect($line->fresh()->meta)->toBeNull()
        ->and(SearchEvent::query()->count())->toBe(0);
});

it('ignores expired attribution', function () {
    app(Attribution::class)->remember($this->variant->product_id, $this->search->id, 1, 'organic', 'cart:7');
    $this->travel(31)->minutes();

    CartLine::factory()->create(['purchasable_id' => $this->variant->id]);

    expect(SearchEvent::query()->count())->toBe(0)
        ->and(app(Attribution::class)->find($this->variant->product_id))->toBeNull();
});

it('records a purchase for every placed order line carrying attribution', function () {
    $order = Order::factory()->create();
    OrderLine::factory()->create([
        'order_id' => $order->id,
        'purchasable_id' => $this->variant->id,
        'meta' => ['search_attribution' => ['search_id' => $this->search->id, 'position' => 1, 'source' => 'learned', 'session_id' => 'cart:7']],
    ]);
    OrderLine::factory()->create(['order_id' => $order->id]);

    event(new OrderPlaced($order));

    expect(SearchEvent::query()->sole())->toMatchArray([
        'search_id' => $this->search->id,
        'product_id' => $this->variant->product_id,
        'position' => 1,
        'type' => 'purchase',
        'source' => 'learned',
        'session_id' => 'cart:7',
    ]);
});

it('is null-safe without a session', function () {
    $attribution = new Attribution(app('config'), null);

    $attribution->remember(1, $this->search->id, 1, 'organic', 'cart:1');

    expect($attribution->find(1))->toBeNull();
});
