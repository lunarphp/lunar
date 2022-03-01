<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components\Orders;

use GetCandy\Hub\Http\Livewire\Components\Orders\OrderShow;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Order;
use GetCandy\Models\OrderLine;
use GetCandy\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

/**
 * @group hub.orders
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
        ->test(OrderShow::class, [
            'order' => $order,
        ])->assertSet('order.id', $order->id);
    }

    /** @test */
    public function can_update_status()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $order = Order::factory()->create([
            'user_id'   => null,
            'placed_at' => now(),
            'status' => 'awaiting-payment',
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
        ->test(OrderShow::class, [
            'order' => $order,
        ])->assertSet('order.status', $order->status)
        ->set('order.status', 'foo-bar')
        ->call('updateStatus')
        ->assertHasNoErrors();

        $this->assertEquals('foo-bar', $order->refresh()->status);

        $this->assertDatabaseHas((new Activity)->getTable(), [
            'event' => 'status-update',
            'subject_id' => $order->id,
            'subject_type' => Order::class,
            'causer_id' => $staff->id,
            'properties' => json_encode([
                'new' => 'foo-bar',
                'previous' => 'awaiting-payment',
            ])
        ]);
    }

    /** @test */
    public function can_add_comment()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $order = Order::factory()->create([
            'user_id'   => null,
            'placed_at' => now(),
            'status' => 'awaiting-payment',
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
        ->test(OrderShow::class, [
            'order' => $order,
        ])->assertSet('order.status', $order->status)
        ->set('comment', 'Testing 123')
        ->call('addComment')
        ->assertHasNoErrors();

        $this->assertDatabaseHas((new Activity)->getTable(), [
            'event' => 'comment',
            'subject_id' => $order->id,
            'subject_type' => Order::class,
            'causer_id' => $staff->id,
            'properties' => json_encode([
                'content' => 'Testing 123',
            ])
        ]);
    }
}
