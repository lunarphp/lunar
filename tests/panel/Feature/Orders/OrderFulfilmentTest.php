<?php

use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentLine;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
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

/**
 * A pending shipping fulfilment covering one order line at the given quantity.
 */
function pendingShippingFulfilment(Order $order, int $quantity = 2): Fulfilment
{
    $line = OrderLine::factory()->for($order)->create([
        'type' => 'physical',
        'requires_fulfilment' => true,
        'quantity' => $quantity,
    ]);

    $fulfilment = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'pending']);
    FulfilmentLine::factory()->create([
        'fulfilment_id' => $fulfilment->id,
        'order_line_id' => $line->id,
        'quantity' => $quantity,
    ]);

    return $fulfilment;
}

it('no longer exposes a create-fulfilment endpoint', function () {
    $order = Order::factory()->placed()->create();

    $this->post(route('panel.orders.show', $order).'/fulfilments')->assertNotFound();
});

it('ships a fulfilment with multiple tracking rows', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'pending']);

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.ship', [$order, $fulfilment]), [
            'tracking' => [
                ['carrier' => 'royal-mail', 'tracking_number' => 'RM123456789GB'],
                ['tracking_number' => 'CUSTOM-1', 'tracking_url' => 'https://example.com/track/CUSTOM-1'],
            ],
            'notify' => false,
        ])
        ->assertSessionHas('success');

    $fulfilment->refresh();
    expect($fulfilment->state::$name)->toBe('shipped');
    expect($fulfilment->trackings()->count())->toBe(2);
});

it('forbids shipping an already shipped fulfilment', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->shipped()->create();

    $this->post(route('panel.orders.fulfilments.ship', [$order, $fulfilment]))->assertForbidden();
});

it('forbids shipping a held fulfilment', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'pending']);
    $fulfilment->hold('out-of-stock');

    $this->post(route('panel.orders.fulfilments.ship', [$order, $fulfilment]))->assertForbidden();
});

it('scopes the fulfilment routes to the order', function () {
    $order = Order::factory()->placed()->create();
    $otherOrder = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($otherOrder)->create(['method' => 'shipping', 'state' => 'pending']);

    $this->post(route('panel.orders.fulfilments.ship', [$order, $fulfilment]))->assertNotFound();
});

it('marks a collection fulfilment collected via fulfil', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->collection()->create(['state' => 'pending']);

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.fulfil', [$order, $fulfilment]), ['notify' => false])
        ->assertSessionHas('success');

    expect($fulfilment->refresh()->state::$name)->toBe('collected');
});

it('forbids fulfil on a tracking method', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'pending']);

    $this->post(route('panel.orders.fulfilments.fulfil', [$order, $fulfilment]))->assertForbidden();
});

it('transitions a fulfilment to an intermediate state', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'pending']);

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.transition', [$order, $fulfilment]), ['state' => 'in-progress'])
        ->assertSessionHas('success');

    expect($fulfilment->refresh()->state::$name)->toBe('in-progress');
});

it('forbids transitioning back to the default state', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'in-progress']);

    // in-progress → pending is legal in the graph but reserved for the
    // destructive cancel endpoint, not the update-status menu.
    $this->post(route('panel.orders.fulfilments.transition', [$order, $fulfilment]), ['state' => 'pending'])
        ->assertForbidden();
});

it('forbids transitioning to a terminal state that has a dedicated endpoint', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'pending']);

    $this->post(route('panel.orders.fulfilments.transition', [$order, $fulfilment]), ['state' => 'shipped'])
        ->assertForbidden();
});

it('splits quantities into a new fulfilment', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = pendingShippingFulfilment($order, quantity: 5);
    $lineId = $fulfilment->lines()->first()->order_line_id;

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.split', [$order, $fulfilment]), [
            'moves' => [$lineId => 2],
        ])
        ->assertSessionHas('success');

    expect($order->fulfilments()->count())->toBe(2);
    expect($fulfilment->refresh()->lines()->sum('quantity'))->toBe(3);
    expect($order->fulfilments()->whereKeyNot($fulfilment->id)->first()->lines()->sum('quantity'))->toBe(2);
});

it('rejects a split that exceeds the allocation', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = pendingShippingFulfilment($order, quantity: 2);
    $lineId = $fulfilment->lines()->first()->order_line_id;

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.split', [$order, $fulfilment]), [
            'moves' => [$lineId => 3],
        ])
        ->assertSessionHasErrors('moves');
});

it('rejects a split that moves everything out', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = pendingShippingFulfilment($order, quantity: 2);
    $lineId = $fulfilment->lines()->first()->order_line_id;

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.split', [$order, $fulfilment]), [
            'moves' => [$lineId => 2],
        ])
        ->assertSessionHasErrors('moves');
});

it('forbids splitting a shipped fulfilment', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->shipped()->create();

    $this->post(route('panel.orders.fulfilments.split', [$order, $fulfilment]), ['moves' => [1 => 1]])
        ->assertForbidden();
});

it('merges a fulfilment into a matching target', function () {
    $order = Order::factory()->placed()->create();
    $location = Location::factory()->create();

    $source = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'pending', 'location_id' => $location->id]);
    $target = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'pending', 'location_id' => $location->id]);

    $lineA = OrderLine::factory()->for($order)->create(['type' => 'physical', 'requires_fulfilment' => true, 'quantity' => 2]);
    $lineB = OrderLine::factory()->for($order)->create(['type' => 'physical', 'requires_fulfilment' => true, 'quantity' => 1]);
    FulfilmentLine::factory()->create(['fulfilment_id' => $source->id, 'order_line_id' => $lineA->id, 'quantity' => 2]);
    FulfilmentLine::factory()->create(['fulfilment_id' => $target->id, 'order_line_id' => $lineB->id, 'quantity' => 1]);

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.merge', [$order, $source]), ['target_id' => $target->id])
        ->assertSessionHas('success');

    // Moving every line empties the source, which is removed.
    expect($order->fulfilments()->count())->toBe(1);
    expect($target->refresh()->lines()->sum('quantity'))->toBe(3);
});

it('rejects merging into a fulfilment of a different method', function () {
    $order = Order::factory()->placed()->create();
    $location = Location::factory()->create();

    $source = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'pending', 'location_id' => $location->id]);
    $target = Fulfilment::factory()->for($order)->collection()->create(['state' => 'pending', 'location_id' => $location->id]);

    $lineA = OrderLine::factory()->for($order)->create(['type' => 'physical', 'requires_fulfilment' => true, 'quantity' => 1]);
    FulfilmentLine::factory()->create(['fulfilment_id' => $source->id, 'order_line_id' => $lineA->id, 'quantity' => 1]);

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.merge', [$order, $source]), ['target_id' => $target->id])
        ->assertSessionHasErrors('target_id');
});

it('marks a shipped fulfilment returned', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->shipped()->create();

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.return', [$order, $fulfilment]), ['notify' => false])
        ->assertSessionHas('success');

    expect($fulfilment->refresh()->state::$name)->toBe('returned');
});

it('undoes a return, keeping the handover', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->returned()->create();

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.undo-return', [$order, $fulfilment]), ['notify' => false])
        ->assertSessionHas('success');

    $fulfilment->refresh();
    expect($fulfilment->state::$name)->toBe('shipped');
    // The original handover timestamp survives the round trip.
    expect($fulfilment->shipped_at)->not->toBeNull();
});

it('forbids undoing a return on a fulfilment that is not returned', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->shipped()->create();

    $this->post(route('panel.orders.fulfilments.undo-return', [$order, $fulfilment]))->assertForbidden();
});

it('holds and releases a fulfilment', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'pending']);

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.hold', [$order, $fulfilment]), [
            'reason' => 'out-of-stock',
            'note' => 'Restock due Friday.',
        ])
        ->assertSessionHas('success');

    $fulfilment->refresh();
    expect($fulfilment->isOnHold())->toBeTrue();
    expect($fulfilment->hold_reason)->toBe('out-of-stock');

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.release', [$order, $fulfilment]))
        ->assertSessionHas('success');

    expect($fulfilment->refresh()->isOnHold())->toBeFalse();
});

it('rejects an unknown hold reason', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'pending']);

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.hold', [$order, $fulfilment]), ['reason' => 'nonsense'])
        ->assertSessionHasErrors('reason');
});

it('forbids holding a shipped fulfilment', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->shipped()->create();

    $this->post(route('panel.orders.fulfilments.hold', [$order, $fulfilment]))->assertForbidden();
});

it('cancels a shipped fulfilment back to the pool', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->shipped()->create();
    $fulfilment->trackings()->create(['tracking_number' => 'RM1']);

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.cancel', [$order, $fulfilment]))
        ->assertSessionHas('success');

    $fulfilment->refresh();
    expect($fulfilment->state::$name)->toBe('pending');
    expect($fulfilment->shipped_at)->toBeNull();
    expect($fulfilment->trackings()->count())->toBe(0);
});

it('forbids cancelling an unprogressed fulfilment', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'pending']);

    $this->post(route('panel.orders.fulfilments.cancel', [$order, $fulfilment]))->assertForbidden();
});

it('changes a pending fulfilment location', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'pending']);
    $location = Location::factory()->create();

    $this->from(route('panel.orders.show', $order))
        ->put(route('panel.orders.fulfilments.location.update', [$order, $fulfilment]), [
            'location_id' => $location->id,
        ])
        ->assertSessionHas('success');

    expect($fulfilment->refresh()->location_id)->toBe($location->id);
});

it('forbids changing the location of a shipped fulfilment', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->shipped()->create();

    $this->put(route('panel.orders.fulfilments.location.update', [$order, $fulfilment]), [
        'location_id' => Location::factory()->create()->id,
    ])->assertForbidden();
});

it('adds tracking to a shipped fulfilment', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->shipped()->create();

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.fulfilments.trackings.store', [$order, $fulfilment]), [
            'carrier' => 'royal-mail',
            'tracking_number' => 'RM987654321GB',
        ])
        ->assertSessionHas('success');

    expect($fulfilment->trackings()->count())->toBe(1);
});

it('forbids adding tracking before handover', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->create(['method' => 'shipping', 'state' => 'pending']);

    $this->post(route('panel.orders.fulfilments.trackings.store', [$order, $fulfilment]), [
        'tracking_number' => 'RM1',
    ])->assertForbidden();
});

it('removes a tracking reference, scoped to the fulfilment', function () {
    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->shipped()->create();
    $tracking = $fulfilment->trackings()->create(['tracking_number' => 'RM1']);

    $other = Fulfilment::factory()->for($order)->shipped()->create();
    $foreign = $other->trackings()->create(['tracking_number' => 'RM2']);

    $this->delete(route('panel.orders.fulfilments.trackings.destroy', [$order, $fulfilment, $foreign]))
        ->assertNotFound();

    $this->from(route('panel.orders.show', $order))
        ->delete(route('panel.orders.fulfilments.trackings.destroy', [$order, $fulfilment, $tracking]))
        ->assertSessionHas('success');

    expect($fulfilment->trackings()->count())->toBe(0);
});
