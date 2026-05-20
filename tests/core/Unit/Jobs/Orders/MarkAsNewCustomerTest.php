<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Jobs\Orders\MarkAsNewCustomer;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderAddress;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can correctly mark order for new customer', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    $order = Order::factory()->create([
        'new_customer' => false,
        'placed_at' => now()->subYear(),
    ]);

    OrderAddress::factory()->create([
        'order_id' => $order->id,
        'contact_email' => 'customer@site.com',
        'type' => 'billing',
    ]);

    MarkAsNewCustomer::dispatchSync($order->id);

    expect($order->refresh()->new_customer)->toBeTrue();

    $order = Order::factory()->create([
        'new_customer' => false,
        'placed_at' => now(),
    ]);

    OrderAddress::factory()->create([
        'order_id' => $order->id,
        'contact_email' => 'customer@site.com',
        'type' => 'billing',
    ]);

    MarkAsNewCustomer::dispatchSync($order->id);

    expect($order->refresh()->new_customer)->toBeFalse();
});
