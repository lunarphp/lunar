<?php

uses(\Lunar\Tests\Core\TestCase::class);
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

test('can generate reference with default config', function () {
    $order = Order::factory()->create([
        'reference' => null,
        'placed_at' => now(),
    ]);

    \Illuminate\Support\Facades\Config::set('lunar.orders.reference_format', []);

    expect($order->reference)->toBeNull();

    $result = app(OrderReferenceGenerator::class)->generate($order);

    expect($result)->toEqual('00000001');
})->group('reference');

test('can generate reference with different config', function ($length, $character, $direction, $prefix, $expected) {
    $order = Order::factory()->create([
        'reference' => null,
        'placed_at' => now(),
    ]);

    \Illuminate\Support\Facades\Config::set('lunar.orders.reference_format', [
        'prefix' => $prefix,
        'padding_direction' => $direction,
        'padding_character' => $character,
        'length' => $length,
    ]);

    expect($order->reference)->toBeNull();

    $result = app(OrderReferenceGenerator::class)->generate($order);

    expect($result)->toEqual($expected);
})->with([
    ['length' => 8, 'character' => 0, 'direction' => STR_PAD_LEFT, 'prefix' => '', 'expected' => '00000001'],
    ['length' => 0, 'character' => 0, 'direction' => STR_PAD_LEFT, 'prefix' => '', 'expected' => '1'],
    ['length' => 8, 'character' => 0, 'direction' => STR_PAD_BOTH, 'prefix' => '', 'expected' => '00010000'],
    ['length' => 8, 'character' => 'A', 'direction' => STR_PAD_RIGHT, 'prefix' => '', 'expected' => '1AAAAAAA'],
    ['length' => 8, 'character' => '0', 'direction' => STR_PAD_LEFT, 'prefix' => 'A', 'expected' => 'A00000001'],
])->group('reference2');
