<?php

namespace GetCandy\Tests\Unit\Base;

use GetCandy\Base\OrderReferenceGenerator;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Order;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group reference
 */
class OrderReferenceGeneratorTest extends TestCase
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
    public function can_generate_reference()
    {
        $order = Order::factory()->create([
            'reference' => null,
            'placed_at' => now(),
        ]);

        $this->assertNull($order->reference);

        $result = app(OrderReferenceGenerator::class)->generate($order);

        $this->assertEquals($order->created_at->format('Y-m').'-0001', $result);
    }

    /** @test  */
    public function can_increment_order_reference_by_default()
    {
        $orderA = Order::factory()->create([
            'reference' => null,
            'placed_at' => now(),
        ]);

        $orderA->update([
            'reference' => app(OrderReferenceGenerator::class)->generate($orderA),
        ]);

        $this->assertEquals($orderA->created_at->format('Y-m').'-0001', $orderA->reference);

        $order = Order::factory()->create([
            'reference' => null,
            'placed_at' => now(),
        ]);

        $result = app(OrderReferenceGenerator::class)->generate($order);

        $this->assertEquals($order->created_at->format('Y-m').'-0002', $result);
    }

    /** @test */
    public function can_override_reference_generator()
    {
        $order = Order::factory()->create([
            'reference' => null,
            'placed_at' => now(),
        ]);

        $this->assertNull($order->reference);

        $result = app(OrderReferenceGenerator::class)
            ->override(function ($order) {
                return 'hello-'.$order->id;
            })->generate($order);

        $this->assertEquals('hello-'.$order->id, $result);
    }
}
