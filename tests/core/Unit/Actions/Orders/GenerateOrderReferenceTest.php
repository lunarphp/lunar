<?php

uses(\Lunar\Tests\TestCase::class);

use Illuminate\Support\Facades\Config;
use Lunar\Actions\Orders\GenerateOrderReference;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Stubs\TestOrderReferenceGenerator;

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

    $result = app(GenerateOrderReference::class)->execute($order);

    expect($result)->toEqual($order->created_at->format('Y-m').'-0001');
});

test('can override generator via config', function () {
    $order = Order::factory()->create([
        'reference' => null,
        'placed_at' => now(),
    ]);

    Config::set('lunar.orders.reference_generator', TestOrderReferenceGenerator::class);

    expect($order->reference)->toBeNull();

    $result = app(GenerateOrderReference::class)->execute($order);

    expect($result)->toEqual('reference-'.$order->id);
});

test('can set generator to null', function () {
    $order = Order::factory()->create([
        'reference' => null,
        'placed_at' => now(),
    ]);

    Config::set('lunar.orders.reference_generator', null);

    expect($order->reference)->toBeNull();

    $result = app(GenerateOrderReference::class)->execute($order);

    expect($result)->toBeNull();
});
