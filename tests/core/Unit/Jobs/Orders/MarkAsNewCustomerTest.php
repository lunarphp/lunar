<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\Jobs\Orders\MarkAsNewCustomer;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

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

    MarkAsNewCustomer::dispatch($order->id);

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

    MarkAsNewCustomer::dispatch($order->id);

    expect($order->refresh()->new_customer)->toBeFalse();
});
