<?php

use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);
    Language::factory()->create(['default' => true, 'code' => 'en']);
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
});

it('creates a fulfilment covering outstanding lines', function () {
    $order = Order::factory()->placed()->create();
    OrderLine::factory()->for($order)->create([
        'type' => 'physical',
        'requires_fulfilment' => true,
        'quantity' => 2,
    ]);

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.store', $order))
        ->assertSessionHas('success');

    expect($order->fulfilments()->count())->toBe(1);
    expect($order->fulfilments()->first()->lines()->sum('quantity'))->toBe(2);
});

it('forbids creating a fulfilment when nothing is outstanding', function () {
    $order = Order::factory()->placed()->create();

    $this->post(route('panel.orders.fulfilments.store', $order))->assertForbidden();
});

it('ships a fulfilment with tracking', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'pending']);

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.ship', [$order, $fulfilment]), [
            'carrier' => 'royal-mail',
            'tracking_number' => 'RM123456789GB',
            'notify' => false,
        ])
        ->assertSessionHas('success');

    $fulfilment->refresh();
    expect($fulfilment->state::$name)->toBe('shipped');
    expect($fulfilment->trackings()->count())->toBe(1);
});

it('forbids shipping an already shipped fulfilment', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->shipped()->create();

    $this->post(route('panel.orders.fulfilments.ship', [$order, $fulfilment]))->assertForbidden();
});

it('scopes the ship route to the order', function () {
    $order = Order::factory()->placed()->create();
    $otherOrder = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($otherOrder)->create(['method' => 'shipping', 'state' => 'pending']);

    $this->post(route('panel.orders.fulfilments.ship', [$order, $fulfilment]))->assertNotFound();
});
