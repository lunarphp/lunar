<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Core\Actions\Carts\MergeCart;
use Lunar\Core\Events\Carts\CartCreated;
use Lunar\Core\Events\Carts\CartDeleted;
use Lunar\Core\Events\Carts\CartMerged;
use Lunar\Core\Events\Customers\CustomerCreated;
use Lunar\Core\Events\Customers\CustomerDeleted;
use Lunar\Core\Events\Customers\CustomerUpdated;
use Lunar\Core\Events\Discounts\DiscountCreated;
use Lunar\Core\Events\Discounts\DiscountDeleted;
use Lunar\Core\Events\Discounts\DiscountUpdated;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Language;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
    Channel::factory()->create(['default' => true]);
});

test('customer lifecycle dispatches created, updated and deleted', function () {
    Event::fake([CustomerCreated::class, CustomerUpdated::class, CustomerDeleted::class]);

    $customer = Customer::factory()->create();
    Event::assertDispatched(CustomerCreated::class, fn ($e) => $e->customer->is($customer));

    $customer->update(['meta' => ['vip' => true]]);
    Event::assertDispatched(CustomerUpdated::class, fn ($e) => $e->customer->is($customer));

    $customer->delete();
    Event::assertDispatched(CustomerDeleted::class, fn ($e) => $e->customer->is($customer));
});

test('discount lifecycle dispatches created, updated and deleted', function () {
    Event::fake([DiscountCreated::class, DiscountUpdated::class, DiscountDeleted::class]);

    $discount = Discount::factory()->create();
    Event::assertDispatched(DiscountCreated::class, fn ($e) => $e->discount->is($discount));

    $discount->update(['name' => 'Updated']);
    Event::assertDispatched(DiscountUpdated::class, fn ($e) => $e->discount->is($discount));

    $discount->delete();
    Event::assertDispatched(DiscountDeleted::class, fn ($e) => $e->discount->is($discount));
});

test('cart dispatches created and deleted', function () {
    Event::fake([CartCreated::class, CartDeleted::class]);

    $cart = Cart::factory()->create();
    Event::assertDispatched(CartCreated::class, fn ($e) => $e->cart->is($cart));

    $cart->delete();
    Event::assertDispatched(CartDeleted::class, fn ($e) => $e->cart->is($cart));
});

test('merging carts dispatches cart merged', function () {
    $target = Cart::factory()->create();
    $source = Cart::factory()->create();

    Event::fake([CartMerged::class]);

    app(MergeCart::class)->execute($target, $source);

    Event::assertDispatched(
        CartMerged::class,
        fn ($e) => $e->target->is($target) && $e->source->is($source),
    );
});

test('merging a cart into itself dispatches nothing', function () {
    $cart = Cart::factory()->create();

    Event::fake([CartMerged::class]);

    app(MergeCart::class)->execute($cart, $cart);

    Event::assertNotDispatched(CartMerged::class);
});
