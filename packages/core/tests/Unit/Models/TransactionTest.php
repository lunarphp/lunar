<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Order;
use GetCandy\Models\Transaction;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.transactions
 */
class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Language::factory()->create([
            'default' => true,
            'code'    => 'en',
        ]);

        Currency::factory()->create([
            'default'        => true,
            'decimal_places' => 2,
        ]);
    }

    /** @test */
    public function can_make_transaction()
    {
        Currency::factory()->create([
            'default' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => null,
        ]);

        $transaction = Transaction::factory()->create([
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseHas((new Order())->getTable(), $order->getRawOriginal());

        $this->assertDatabaseHas((new Transaction())->getTable(), $transaction->getRawOriginal());
    }

    /** @test */
    public function can_store_last_four_correctly()
    {
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
                (new Transaction())->getTable(),
                [
                    'id' => $transaction->id,
                    'last_four' => $check,
                ]
            );
        }
    }
}
