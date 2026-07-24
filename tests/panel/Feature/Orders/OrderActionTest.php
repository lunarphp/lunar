<?php

use Illuminate\Support\Facades\Notification;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\Transaction;
use Lunar\Core\Notifications\OrderUpdate;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
});

it('closes and reopens an order', function () {
    $order = Order::factory()->placed()->create();

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.close', $order))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($order->refresh()->isClosed())->toBeTrue();

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.reopen', $order))
        ->assertSessionHas('success');

    expect($order->refresh()->isOpen())->toBeTrue();
});

it('forbids closing an already closed order', function () {
    $order = Order::factory()->placed()->closed()->create();

    $this->post(route('panel.orders.close', $order))->assertForbidden();
});

it('forbids reopening a cancelled order', function () {
    $order = Order::factory()->placed()->closed()->create(['cancelled_at' => now()]);

    $this->post(route('panel.orders.reopen', $order))->assertForbidden();
});

it('cancels an order with a reason', function () {
    $order = Order::factory()->placed()->create();

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.cancel', $order), ['reason' => 'customer', 'note' => 'Changed mind', 'notify' => false])
        ->assertSessionHas('success');

    $order->refresh();
    expect($order->isCancelled())->toBeTrue();
    expect($order->cancel_reason)->toBe('customer');
});

it('forbids cancelling once something has shipped', function () {
    $order = Order::factory()->placed()->create();
    Fulfilment::factory()->for($order)->create(['state' => 'shipped']);

    $this->post(route('panel.orders.cancel', $order))->assertForbidden();
});

it('forbids capture when there is no payment intent', function () {
    $order = Order::factory()->placed()->create();

    $this->post(route('panel.orders.capture', $order), ['transaction_id' => 1, 'amount' => 10])
        ->assertForbidden();
});

it('captures against a payment intent', function () {
    $order = Order::factory()->placed()->create(['total' => 12000]);
    $intent = Transaction::factory()->for($order)->create(['type' => 'intent', 'success' => true, 'driver' => 'offline', 'amount' => 12000]);

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.capture', $order), ['transaction_id' => $intent->id, 'amount' => 120])
        ->assertSessionHas('success');
});

it('forbids refund when there is no capture', function () {
    $order = Order::factory()->placed()->create();

    $this->post(route('panel.orders.refund', $order), ['transaction_id' => 1, 'amount' => 10])
        ->assertForbidden();
});

it('refunds against a capture within the available balance', function () {
    $order = Order::factory()->placed()->create(['total' => 12000]);
    $capture = Transaction::factory()->for($order)->create(['type' => 'capture', 'success' => true, 'driver' => 'offline', 'amount' => 12000]);

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.refund', $order), ['transaction_id' => $capture->id, 'amount' => 50, 'notes' => 'Partial'])
        ->assertSessionHas('success');
});

it('sends a customer notification', function () {
    Notification::fake();

    $order = Order::factory()->placed()->create();
    $order->addresses()->create(['type' => 'billing', 'contact_email' => 'ada@example.com']);

    $this->from(route('panel.orders.show', $order))
        ->post(route('panel.orders.notify', $order), ['notification' => 'order-update', 'message' => 'Thanks!'])
        ->assertSessionHas('success');

    Notification::assertSentOnDemand(OrderUpdate::class);
});

it('saves an internal note', function () {
    $order = Order::factory()->placed()->create();

    $this->from(route('panel.orders.show', $order))
        ->put(route('panel.orders.note.update', $order), ['notes' => 'Handle with care'])
        ->assertSessionHas('success');

    expect($order->refresh()->notes)->toBe('Handle with care');
});

it('syncs tags, upper-casing values', function () {
    $order = Order::factory()->placed()->create();

    $this->from(route('panel.orders.show', $order))
        ->put(route('panel.orders.tags.update', $order), ['tags' => ['vip', 'wholesale']])
        ->assertSessionHas('success');

    expect($order->refresh()->tags->pluck('value')->all())->toEqualCanonicalizing(['VIP', 'WHOLESALE']);
});

it('forbids order actions for staff without permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $order = Order::factory()->placed()->create();

    $this->post(route('panel.orders.close', $order))->assertForbidden();
});
