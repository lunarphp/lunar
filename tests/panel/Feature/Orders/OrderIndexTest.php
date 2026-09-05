<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\Tag;
use Lunar\Core\States\Order\Fulfilment\Fulfilled;
use Lunar\Core\States\Order\Fulfilment\Unfulfilled;
use Lunar\Core\States\Order\Payment\Authorized;
use Lunar\Core\States\Order\Payment\Paid;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('redirects guests away from the order index', function () {
    $this->get(route('panel.orders.index'))->assertRedirect(route('panel.login'));
});

it('renders the order index for authenticated staff', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Order::factory()->placed()->count(3)->create();

    $this->get(route('panel.orders.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/Index')
            ->has('orders.data', 3)
            ->has('columns')
            ->has('channels')
            ->has('paymentOptions')
            ->has('fulfilmentOptions')
            ->has('urls.index')
        );
});

it('searches orders by reference', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $match = Order::factory()->placed()->create(['reference' => 'REF-MATCH']);
    Order::factory()->placed()->create(['reference' => 'REF-OTHER']);

    $this->get(route('panel.orders.index', ['q' => 'MATCH']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.id', $match->id)
        );
});

it('searches orders by billing customer name', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $match = Order::factory()->placed()->create();
    $match->addresses()->create(['type' => 'billing', 'first_name' => 'Ada', 'last_name' => 'Lovelace']);
    Order::factory()->placed()->create();

    $this->get(route('panel.orders.index', ['q' => 'Lovelace']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.id', $match->id)
        );
});

it('filters orders by payment status', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $paid = Order::factory()->placed()->create(['payment_status' => Paid::class]);
    Order::factory()->placed()->create(['payment_status' => Authorized::class]);

    $this->get(route('panel.orders.index', ['payment_status' => 'paid']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.id', $paid->id)
        );
});

it('filters orders by fulfilment status', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $fulfilled = Order::factory()->placed()->create(['fulfilment_status' => Fulfilled::class]);
    Order::factory()->placed()->create(['fulfilment_status' => Unfulfilled::class]);

    $this->get(route('panel.orders.index', ['fulfilment_status' => 'fulfilled']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.id', $fulfilled->id)
        );
});

it('filters orders by channel', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $channel = Channel::factory()->create();
    $match = Order::factory()->placed()->create(['channel_id' => $channel->id]);
    Order::factory()->placed()->create();

    $this->get(route('panel.orders.index', ['channel_id' => $channel->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.id', $match->id)
        );
});

it('filters orders by tag', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $match = Order::factory()->placed()->create();
    $match->tags()->attach(Tag::factory()->create(['value' => 'VIP']));
    Order::factory()->placed()->create();

    $this->get(route('panel.orders.index', ['tag' => 'VIP']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.id', $match->id)
        );
});

it('defaults to open orders only', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $open = Order::factory()->placed()->create();
    Order::factory()->placed()->closed()->create();
    Order::factory()->placed()->create(['cancelled_at' => now()]);

    $this->get(route('panel.orders.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.id', $open->id)
            ->where('filters.lifecycle', 'open')
        );
});

it('shows every order when the lifecycle filter is all', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Order::factory()->placed()->create();
    Order::factory()->placed()->closed()->create();
    Order::factory()->placed()->create(['cancelled_at' => now()]);

    $this->get(route('panel.orders.index', ['lifecycle' => 'all']))
        ->assertInertia(fn (Assert $page) => $page->has('orders.data', 3));
});

it('filters orders by cancelled lifecycle state', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $cancelled = Order::factory()->placed()->create(['cancelled_at' => now()]);
    Order::factory()->placed()->create();

    $this->get(route('panel.orders.index', ['lifecycle' => 'cancelled']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.id', $cancelled->id)
        );
});

it('filters orders by a placed-at date preset', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $recent = Order::factory()->placed()->create(['placed_at' => now()->subDays(2)]);
    Order::factory()->placed()->create(['placed_at' => now()->subDays(40)]);

    $this->get(route('panel.orders.index', ['date' => '30d']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.id', $recent->id)
        );
});

it('sorts orders by total ascending', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $cheap = Order::factory()->placed()->create(['total' => 1000]);
    $pricey = Order::factory()->placed()->create(['total' => 9000]);

    $this->get(route('panel.orders.index', ['sort' => 'total', 'direction' => 'asc']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('orders.data.0.id', $cheap->id)
            ->where('orders.data.1.id', $pricey->id)
        );
});

it('exposes shaped row fields', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    $order = Order::factory()->placed()->create([
        'reference' => 'REF-1',
        'total' => 12000,
        'payment_status' => Paid::class,
        'fulfilment_status' => Unfulfilled::class,
    ]);
    $order->addresses()->create(['type' => 'billing', 'first_name' => 'Ada', 'last_name' => 'Lovelace', 'contact_email' => 'ada@example.com']);
    $order->tags()->attach(Tag::factory()->create(['value' => 'VIP']));

    $this->get(route('panel.orders.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('orders.data.0.reference', 'REF-1')
            ->where('orders.data.0.customer_name', 'Ada Lovelace')
            ->where('orders.data.0.customer_email', 'ada@example.com')
            ->where('orders.data.0.payment_status', 'paid')
            ->where('orders.data.0.fulfilment_status', 'unfulfilled')
            ->where('orders.data.0.total', '£120.00')
            ->where('orders.data.0.tags.0', 'VIP')
        );
});

it('exposes real KPI counts', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    Order::factory()->placed()->create(['payment_status' => Authorized::class, 'total' => 5000, 'exchange_rate' => 1]);
    Order::factory()->placed()->create(['payment_status' => Paid::class, 'fulfilment_status' => Unfulfilled::class, 'total' => 10000, 'exchange_rate' => 1]);
    Order::factory()->create(['placed_at' => null, 'total' => 99999]);

    $this->get(route('panel.orders.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('kpis.orders30d', 2)
            ->where('kpis.revenue30d', '£150.00')
            ->where('kpis.awaitingPayment', 1)
            ->where('kpis.awaitingFulfilment', 1)
        );
});

it('paginates the order index', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Order::factory()->placed()->count(20)->create();

    $this->get(route('panel.orders.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('orders.data', 15)
            ->where('orders.total', 20)
            ->where('orders.last_page', 2)
        );

    $this->get(route('panel.orders.index', ['page' => 2]))
        ->assertInertia(fn (Assert $page) => $page->has('orders.data', 5));
});

it('forbids staff without the manage-orders permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->get(route('panel.orders.index'))->assertForbidden();
});
