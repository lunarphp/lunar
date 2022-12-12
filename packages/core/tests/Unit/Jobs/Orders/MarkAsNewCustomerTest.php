<?php

namespace Lunar\Tests\Unit\Jobs\Collections;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Jobs\Orders\MarkAsNewCustomer;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Tests\TestCase;

/**
 * @group lunar.jobs.orders
 */
class MarkAsNewCustomerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_correctly_mark_order_for_new_customer()
    {
        $order = Order::factory()->create([
            'new_customer' => false,
            'placed_at' => now()->subYear(),
        ]);

        OrderAddress::factory()->create([
            'order_id' => $order->id,
            'contact_email' => 'customer@site.com',
            'type' => 'billing',
        ]);

        MarkAsNewCustomer::dispatch($order);

        $this->assertTrue($order->refresh()->new_customer);

        $order = Order::factory()->create([
            'new_customer' => false,
            'placed_at' => now(),
        ]);

        OrderAddress::factory()->create([
            'order_id' => $order->id,
            'contact_email' => 'customer@site.com',
            'type' => 'billing',
        ]);

        MarkAsNewCustomer::dispatch($order);

        $this->assertFalse($order->refresh()->new_customer);
    }
}
