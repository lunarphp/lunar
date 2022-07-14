<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components\Orders;

use GetCandy\Hub\Http\Livewire\Components\Orders\OrderStatus;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Order;
use GetCandy\Models\OrderLine;
use GetCandy\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

/**
 * @group hub.orders.status
 */
class OrderShowTest extends TestCase
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
    public function can_mount_component()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $order = Order::factory()->create([
            'user_id'   => null,
            'placed_at' => now(),
            'currency_code' => Currency::getDefault()->code,
            'meta'      => [
                'foo' => 'bar',
            ],
            'tax_breakdown' => [
                ['description' => 'VAT', 'percentage' => 20, 'total' => 200],
            ],
        ]);

        $this->assertCount(0, $order->lines);

        OrderLine::factory()->create([
            'purchasable_type' => ProductVariant::class,
            'purchasable_id'   => ProductVariant::factory()->create()->id,
            'order_id' => $order->id,
        ]);

        LiveWire::actingAs($staff, 'staff')
        ->test(OrderStatus::class, [
            'order' => $order,
        ])->assertSet('order.id', $order->id);
    }
}
