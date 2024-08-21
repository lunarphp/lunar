<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

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

test('can make transaction', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    $order = Order::factory()->create([
        'user_id' => null,
    ]);

    $transaction = Transaction::factory()->create([
        'order_id' => $order->id,
    ]);

    $this->assertDatabaseHas((new Order)->getTable(), $order->getRawOriginal());

    $this->assertDatabaseHas((new Transaction)->getTable(), $transaction->getRawOriginal());
});

test('can store last four correctly', function () {
    $checks = [
        '0000',
        '0001',
        '1234',
        '1000',
        '0101',
    ];

    foreach ($checks as $check) {
        $transaction = Transaction::factory()->create([
            'last_four' => $check,
        ]);

        $this->assertDatabaseHas(
            (new Transaction)->getTable(),
            [
                'id' => $transaction->id,
                'last_four' => $check,
            ]
        );
    }
});
