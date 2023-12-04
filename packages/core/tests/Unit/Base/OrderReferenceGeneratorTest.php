<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Base\OrderReferenceGenerator;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Order;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);
});

test('can generate reference', function () {
    $order = Order::factory()->create([
        'reference' => null,
        'placed_at' => now(),
    ]);

    expect($order->reference)->toBeNull();

    $result = app(OrderReferenceGenerator::class)->generate($order);

    expect($result)->toEqual($order->created_at->format('Y-m').'-0001');
});

/** @test  */
function can_increment_order_reference_by_default()
{
    $orderA = Order::factory()->create([
        'reference' => null,
        'placed_at' => now(),
    ]);

    $orderA->update([
        'reference' => app(OrderReferenceGenerator::class)->generate($orderA),
    ]);

    expect($orderA->reference)->toEqual($orderA->created_at->format('Y-m').'-0001');

    $order = Order::factory()->create([
        'reference' => null,
        'placed_at' => now(),
    ]);

    $result = app(OrderReferenceGenerator::class)->generate($order);

    expect($result)->toEqual($order->created_at->format('Y-m').'-0002');
}
