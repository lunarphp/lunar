<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentLine;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\Tag;
use Lunar\Core\Models\Transaction;
use Lunar\Core\States\Order\Payment\Paid;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);
    // OrderLine's purchasable factory creates a product, whose URL generator needs a default language.
    Language::factory()->create(['default' => true, 'code' => 'en']);
});

it('redirects guests away from an order', function () {
    $order = Order::factory()->placed()->create();

    $this->get(route('panel.orders.show', $order))->assertRedirect(route('panel.login'));
});

it('renders the order view with shaped props', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $order = Order::factory()->placed()->create([
        'reference' => 'REF-1',
        'sub_total' => 10000,
        'tax_total' => 2000,
        'shipping_total' => 0,
        'total' => 12000,
        'payment_status' => Paid::class,
    ]);
    $line = OrderLine::factory()->for($order)->create([
        'type' => 'physical',
        'description' => 'Widget',
        'identifier' => 'SKU-1',
        'quantity' => 2,
        'unit_price' => 5000,
        'total' => 10000,
    ]);
    $order->addresses()->create(['type' => 'billing', 'first_name' => 'Ada', 'last_name' => 'Lovelace', 'contact_email' => 'ada@example.com']);
    $order->addresses()->create(['type' => 'shipping', 'first_name' => 'Ada', 'last_name' => 'Lovelace', 'city' => 'London', 'postcode' => 'E1 6AN']);
    $order->tags()->attach(Tag::factory()->create(['value' => 'VIP']));

    Transaction::factory()->for($order)->create(['type' => 'capture', 'success' => true, 'amount' => 12000]);
    Transaction::factory()->for($order)->create(['type' => 'refund', 'success' => true, 'amount' => 2000]);

    $fulfilment = Fulfilment::factory()->for($order)->create();
    FulfilmentLine::factory()->create(['fulfilment_id' => $fulfilment->id, 'order_line_id' => $line->id, 'quantity' => 1]);

    $this->get(route('panel.orders.show', $order))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/Show')
            ->where('order.reference', 'REF-1')
            // A full capture plus a partial refund recomputes to partially-refunded.
            ->where('order.payment_status', 'partially-refunded')
            ->has('lines', 1)
            ->where('lines.0.description', 'Widget')
            ->where('lines.0.total', '£100.00')
            ->where('totals.total', '£120.00')
            ->where('totals.refunded', '£20.00')
            ->where('totals.net', '£100.00')
            ->has('transactions', 2)
            ->has('fulfilments', 1)
            ->where('fulfilments.0.lines.0.quantity', 1)
            ->where('billingAddress.first_name', 'Ada')
            ->where('shippingAddress.city', 'London')
            ->where('tags.0', 'VIP')
        );
});

it('defers the activity feed', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $order = Order::factory()->placed()->create();

    $this->get(route('panel.orders.show', $order))
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/Show')
            ->missing('activities')
        );
});

it('forbids staff without the manage-orders permission', function () {
    $order = Order::factory()->placed()->create();

    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->get(route('panel.orders.show', $order))->assertForbidden();
});
