<?php

namespace GetCandy\Tests\Unit\Actions\Orders;

use GetCandy\Actions\Orders\GenerateOrderReference;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Order;
use GetCandy\Tests\Stubs\TestOrderReferenceGenerator;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

/**
 * @group getcandy.actions
 */
class SortProductsByPriceTest extends TestCase
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

        $result = app(GenerateOrderReference::class)->execute($order);

        $this->assertEquals($order->created_at->format('Y-m').'-0001', $result);
    }

    /** @test */
    public function can_override_generator_via_config()
    {
        $order = Order::factory()->create([
            'reference' => null,
            'placed_at' => now(),
        ]);

        Config::set('getcandy.orders.reference_generator', TestOrderReferenceGenerator::class);

        $this->assertNull($order->reference);

        $result = app(GenerateOrderReference::class)->execute($order);

        $this->assertEquals('reference-' . $order->id, $result);
    }

    /** @test */
    public function can_set_generator_to_null()
    {
        $order = Order::factory()->create([
            'reference' => null,
            'placed_at' => now(),
        ]);

        Config::set('getcandy.orders.reference_generator', null);

        $this->assertNull($order->reference);

        $result = app(GenerateOrderReference::class)->execute($order);

        $this->assertNull($result);
    }
}
