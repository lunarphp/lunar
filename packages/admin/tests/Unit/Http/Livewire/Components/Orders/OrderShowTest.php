<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components\Orders;

use GetCandy\Hub\Http\Livewire\Components\Orders\OrderShow;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Country;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Order;
use GetCandy\Models\OrderAddress;
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
            'currency_code' => Currency::getDefault()->code,
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
            ]),
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
            ]),
        ]);
    }

    /** @test */
    public function billing_address_visibility_is_correct()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $order = Order::factory()->create([
            'user_id'   => null,
            'placed_at' => now(),
            'status' => 'awaiting-payment',
            'currency_code' => Currency::getDefault()->code,
            'meta'      => [
                'foo' => 'bar',
            ],
            'tax_breakdown' => [
                ['description' => 'VAT', 'percentage' => 20, 'total' => 200],
            ],
        ]);

        $shipping = OrderAddress::factory()->create([
            'type' => 'shipping',
            'order_id' => $order->id,
            'country_id' => Country::factory()->create()->id,
        ]);

        $billing = $shipping->toArray();
        unset($billing['id']);
        $billing['type'] = 'billing';

        OrderAddress::factory()->create($billing);

        LiveWire::actingAs($staff, 'staff')
        ->test(OrderShow::class, [
            'order' => $order,
        ])->assertSet('order.status', $order->status)
        ->assertSee(
            __('adminhub::components.orders.show.billing_matches_shipping')
        )->set('billingAddress.postcode', 'TX1 1TX')
        ->call('saveBillingAddress')
        ->assertHasNoErrors()
        ->assertDontSee(
            __('adminhub::components.orders.show.billing_matches_shipping')
        );
    }

    /** @test */
    public function can_update_addresses()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $order = Order::factory()->create([
            'user_id'   => null,
            'placed_at' => now(),
            'status' => 'awaiting-payment',
            'currency_code' => Currency::getDefault()->code,
            'meta'      => [
                'foo' => 'bar',
            ],
            'tax_breakdown' => [
                ['description' => 'VAT', 'percentage' => 20, 'total' => 200],
            ],
        ]);

        $shipping = OrderAddress::factory()->create([
            'type' => 'shipping',
            'order_id' => $order->id,
            'country_id' => Country::factory()->create()->id,
        ]);

        $billing = $shipping->toArray();
        unset($billing['id']);
        $billing['type'] = 'billing';

        $billing = OrderAddress::factory()->create($billing);

        LiveWire::actingAs($staff, 'staff')
        ->test(OrderShow::class, [
            'order' => $order,
        ])->assertSet('shippingAddress.id', $shipping->id)
         ->set('shippingAddress.postcode', '1TX RX1')
         ->set('billingAddress.postcode', 'BI1 LL1')
         ->call('saveShippingAddress')
         ->call('saveBillingAddress')
         ->assertHasNoErrors()
         ->assertSet('shippingAddress.postcode', '1TX RX1')
         ->assertSet('billingAddress.postcode', 'BI1 LL1');

        $this->assertEquals($shipping->refresh()->postcode, '1TX RX1');
        $this->assertEquals($billing->refresh()->postcode, 'BI1 LL1');
    }

    /** @test */
    public function requires_capture_displays_correctly()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $order = Order::factory()->create([
            'user_id'   => null,
            'placed_at' => now(),
            'status' => 'awaiting-payment',
            'currency_code' => Currency::getDefault()->code,
            'meta'      => [
                'foo' => 'bar',
            ],
            'tax_breakdown' => [
                ['description' => 'VAT', 'percentage' => 20, 'total' => 200],
            ],
        ]);

        LiveWire::actingAs($staff, 'staff')
        ->test(OrderShow::class, [
            'order' => $order,
        ])->assertSet('order.status', $order->status)
        ->assertDontSee(
            __('adminhub::components.orders.show.capture_payment_btn')
        );

        $order = Order::factory()->create([
            'user_id'   => null,
            'placed_at' => now(),
            'status' => 'awaiting-payment',
            'currency_code' => Currency::getDefault()->code,
            'meta'      => [
                'foo' => 'bar',
            ],
            'tax_breakdown' => [
                ['description' => 'VAT', 'percentage' => 20, 'total' => 200],
            ],
        ]);

        $order->transactions()->create([
            'type' => 'intent',
            'amount' => 100,
            'success' => true,
            'driver' => 'test',
            'reference' => 'TEST123',
            'status' => 'ok',
            'card_type' => 'TEST',
            'last_four' => 1234,
        ]);

        LiveWire::actingAs($staff, 'staff')
        ->test(OrderShow::class, [
            'order' => $order->refresh(),
        ])->assertSet('order.status', $order->status)
        ->assertSee(
            __('adminhub::components.orders.show.capture_payment_btn')
        );
    }
}
