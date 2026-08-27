<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentLine;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\Tag;
use Lunar\Core\Models\Transaction;
use Lunar\Core\States\Order\Payment\Paid;
use Lunar\Core\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Core\ValueObjects\Cart\ShippingBreakdownItem;
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

    $service = OrderLine::factory()->for($order)->create([
        'type' => 'digital',
        'description' => 'Gift wrapping',
        'requires_shipping' => false,
        'requires_fulfilment' => false,
    ]);

    $fulfilment = Fulfilment::factory()->for($order)->create();
    FulfilmentLine::factory()->create(['fulfilment_id' => $fulfilment->id, 'order_line_id' => $line->id, 'quantity' => 1]);

    $this->get(route('panel.orders.show', $order))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/Show')
            ->where('order.reference', 'REF-1')
            // A full capture plus a partial refund recomputes to partially-refunded.
            ->where('order.payment_status', 'partially-refunded')
            // The flat lines table is gone — lines live in their fulfilment.
            ->missing('lines')
            ->has('fulfilments', 1)
            ->where('fulfilments.0.method', 'shipping')
            ->where('fulfilments.0.state', 'pending')
            ->where('fulfilments.0.state_category', 'outstanding')
            ->where('fulfilments.0.on_hold', false)
            ->where('fulfilments.0.lines.0.quantity', 1)
            ->where('fulfilments.0.lines.0.order_line_id', $line->id)
            ->where('fulfilments.0.lines.0.description', 'Widget')
            ->where('fulfilments.0.lines.0.unit_price', '£50.00')
            ->where('fulfilments.0.lines.0.total', '£100.00')
            // Pending shipping parcel: in-progress (transition) + shipped (ship).
            ->has('fulfilments.0.transitions', 2)
            ->where('fulfilments.0.transitions.1.via', 'ship')
            // One unit allocated — nothing to split; nothing to merge into.
            ->where('fulfilments.0.can.split', false)
            ->where('fulfilments.0.can.merge', false)
            ->where('fulfilments.0.can.hold', true)
            ->where('fulfilments.0.can.cancel', false)
            ->has('fulfilments.0.urls.split')
            // The unallocated service line surfaces under other items.
            ->has('otherLines', 1)
            ->where('otherLines.0.description', 'Gift wrapping')
            ->has('holdReasons')
            ->has('locations', 1)
            ->where('totals.total', '£120.00')
            ->where('totals.refunded', '£20.00')
            ->where('totals.net', '£100.00')
            ->has('transactions', 2)
            ->where('billingAddress.first_name', 'Ada')
            ->where('shippingAddress.city', 'London')
            ->has('shippingAddress.update_url')
            ->has('countries')
            ->where('tags.0', 'VIP')
        );
});

it('exposes the checkout delivery method on the page and its dispatchable cards', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $currency = Currency::query()->first();
    $order = Order::factory()->placed()->create();
    $order->shipping_breakdown = new ShippingBreakdown(collect([
        new ShippingBreakdownItem(
            name: 'Royal Mail Tracked 48',
            identifier: 'RM48',
            price: new PriceValue(499, $currency),
        ),
    ]));
    $order->save();

    Fulfilment::factory()->for($order)->create(['state' => 'pending']);
    Fulfilment::factory()->for($order)->digital()->create(['state' => 'pending']);

    $this->get(route('panel.orders.show', $order))
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/Show')
            ->where('shippingOption.name', 'Royal Mail Tracked 48')
            ->where('shippingOption.identifier', 'RM48')
            ->where('shippingOption.price', '£4.99')
            // Dispatchable (tracking) parcels carry the method; digital does not.
            ->where('fulfilments.0.delivery_method', 'Royal Mail Tracked 48')
            ->where('fulfilments.1.delivery_method', null)
        );
});

it('falls back to the shipping line for the delivery method', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $order = Order::factory()->placed()->create();
    OrderLine::factory()->for($order)->create([
        'type' => 'shipping',
        'description' => 'Standard Delivery',
        'requires_shipping' => false,
        'requires_fulfilment' => false,
        'total' => 500,
    ]);

    $this->get(route('panel.orders.show', $order))
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/Show')
            ->where('shippingOption.name', 'Standard Delivery')
            ->where('shippingOption.price', '£5.00')
        );
});

it('offers merge targets between matching outstanding fulfilments', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $order = Order::factory()->placed()->create();
    $location = Location::factory()->create();

    $lineA = OrderLine::factory()->for($order)->create(['type' => 'physical', 'requires_fulfilment' => true, 'quantity' => 2]);
    $lineB = OrderLine::factory()->for($order)->create(['type' => 'physical', 'requires_fulfilment' => true, 'quantity' => 1]);

    $a = Fulfilment::factory()->for($order)->create(['state' => 'pending', 'location_id' => $location->id]);
    $b = Fulfilment::factory()->for($order)->create(['state' => 'pending', 'location_id' => $location->id]);
    FulfilmentLine::factory()->create(['fulfilment_id' => $a->id, 'order_line_id' => $lineA->id, 'quantity' => 2]);
    FulfilmentLine::factory()->create(['fulfilment_id' => $b->id, 'order_line_id' => $lineB->id, 'quantity' => 1]);

    $this->get(route('panel.orders.show', $order))
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/Show')
            ->has('fulfilments', 2)
            ->where('fulfilments.0.can.merge', true)
            ->where('fulfilments.0.can.split', true)
            ->has('fulfilments.0.merge_targets', 1)
            ->where('fulfilments.0.merge_targets.0.id', $b->id)
        );
});

it('hides held-fulfilment ship transitions and flags the hold', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $order = Order::factory()->placed()->create();
    $fulfilment = Fulfilment::factory()->for($order)->create(['state' => 'pending']);
    $fulfilment->hold('out-of-stock', 'Restock due Friday.');

    $this->get(route('panel.orders.show', $order))
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/Show')
            ->where('fulfilments.0.on_hold', true)
            ->where('fulfilments.0.hold_note', 'Restock due Friday.')
            ->where('fulfilments.0.can.release', true)
            ->where('fulfilments.0.can.hold', false)
            // Only the in-progress step remains — shipped is blocked while held.
            ->has('fulfilments.0.transitions', 1)
            ->where('fulfilments.0.transitions.0.via', 'transition')
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
