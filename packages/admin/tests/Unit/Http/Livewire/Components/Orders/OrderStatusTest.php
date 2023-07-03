<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Components\Orders;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Lunar\Hub\Http\Livewire\Components\Orders\OrderStatus;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\Stubs\Mailers\TestAMailer;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;

/**
 * @group hub.orders.status
 */
class OrderStatusTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Language::factory()->create([
            'default' => true,
            'code' => 'en',
        ]);

        Currency::factory()->create([
            'default' => true,
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
            'user_id' => null,
            'placed_at' => now(),
            'currency_code' => Currency::getDefault()->code,
            'meta' => [
                'foo' => 'bar',
            ],
            'tax_breakdown' => [
                ['description' => 'VAT', 'percentage' => 20, 'total' => 200],
            ],
        ]);

        $this->assertCount(0, $order->lines);

        OrderLine::factory()->create([
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => ProductVariant::factory()->create()->id,
            'order_id' => $order->id,
        ]);

        LiveWire::actingAs($staff, 'staff')
            ->test(OrderStatus::class, [
                'order' => $order,
            ])->assertSet('order.id', $order->id);
    }

    /** @test */
    public function default_data_is_set_on_load()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => null,
            'placed_at' => now(),
            'currency_code' => Currency::getDefault()->code,
            'meta' => [
                'foo' => 'bar',
            ],
            'tax_breakdown' => [
                ['description' => 'VAT', 'percentage' => 20, 'total' => 200],
            ],
        ]);

        $this->assertCount(0, $order->lines);

        OrderLine::factory()->create([
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => ProductVariant::factory()->create()->id,
            'order_id' => $order->id,
        ]);

        $component = LiveWire::actingAs($staff, 'staff')
            ->test(OrderStatus::class, [
                'order' => $order,
            ])->assertSet('statuses', config('lunar.orders.statuses'))
            ->assertSet('showStatusSelect', false)
            ->assertSet('newStatus', null)
            ->assertSet('selectedMailers', [])
            ->assertSet('previewTemplate', null)
            ->assertSet('additionalContent', null)
            ->assertSet('emailAddresses', [])
            ->assertSet('additionalEmail', null)
            ->assertSet('phoneNumbers', [])
            ->assertSet('availableNotifications', [])
            ->assertSet('availableMailers', collect());
    }

    /** @test */
    public function can_fetch_available_mailers()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => null,
            'placed_at' => now(),
            'currency_code' => Currency::getDefault()->code,
            'meta' => [
                'foo' => 'bar',
            ],
            'tax_breakdown' => [
                ['description' => 'VAT', 'percentage' => 20, 'total' => 200],
            ],
        ]);

        $this->assertCount(0, $order->lines);

        OrderLine::factory()->create([
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => ProductVariant::factory()->create()->id,
            'order_id' => $order->id,
        ]);

        Config::set('lunar.orders.statuses', [
            'awaiting-payment' => [
                'label' => 'Awaiting Payment',
                'color' => '#848a8c',
                'mailers' => [
                    TestAMailer::class,
                ],
            ],
            'payment-received' => [
                'label' => 'Payment Received',
                'color' => '#6a67ce',
            ],
            'dispatched' => [
                'label' => 'Dispatched',
            ],
        ]);

        $component = LiveWire::actingAs($staff, 'staff')
            ->test(OrderStatus::class, [
                'order' => $order,
            ])->assertSet('statuses', config('lunar.orders.statuses'))
            ->set('newStatus', 'awaiting-payment')
            ->assertCount('availableMailers', 1);

        $this->assertArrayHasKey('test_a_mailer', $component->get('availableMailers'));

        $component->set('newStatus', 'payment-received')
            ->assertCount('availableMailers', 0);
    }

    /** @test */
    public function can_preview_email_template()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => null,
            'placed_at' => now(),
            'currency_code' => Currency::getDefault()->code,
            'meta' => [
                'foo' => 'bar',
            ],
            'tax_breakdown' => [
                ['description' => 'VAT', 'percentage' => 20, 'total' => 200],
            ],
        ]);

        $this->assertCount(0, $order->lines);

        OrderLine::factory()->create([
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => ProductVariant::factory()->create()->id,
            'order_id' => $order->id,
        ]);

        OrderAddress::factory()->create([
            'type' => 'shipping',
            'order_id' => $order->id,
        ]);

        OrderAddress::factory()->create([
            'type' => 'billing',
            'order_id' => $order->id,
        ]);

        Config::set('lunar.orders.statuses', [
            'awaiting-payment' => [
                'label' => 'Awaiting Payment',
                'color' => '#848a8c',
                'mailers' => [
                    TestAMailer::class,
                ],
            ],
        ]);

        $component = LiveWire::actingAs($staff, 'staff')
            ->test(OrderStatus::class, [
                'order' => $order,
            ])->assertSet('statuses', config('lunar.orders.statuses'))
            ->set('newStatus', 'awaiting-payment')
            ->set('previewTemplate', 'test_a_mailer')
            ->assertSet('previewHtml', '<div>Test A Mailer</div>');
    }

    /** @test */
    public function can_update_status()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => null,
            'placed_at' => now(),
            'currency_code' => Currency::getDefault()->code,
            'meta' => [
                'foo' => 'bar',
            ],
            'tax_breakdown' => [
                ['description' => 'VAT', 'percentage' => 20, 'total' => 200],
            ],
        ]);

        $this->assertCount(0, $order->lines);

        OrderLine::factory()->create([
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => ProductVariant::factory()->create()->id,
            'order_id' => $order->id,
        ]);

        OrderAddress::factory()->create([
            'type' => 'shipping',
            'order_id' => $order->id,
        ]);

        OrderAddress::factory()->create([
            'type' => 'billing',
            'order_id' => $order->id,
        ]);

        Config::set('lunar.orders.statuses', [
            'awaiting-payment' => [
                'label' => 'Awaiting Payment',
                'color' => '#848a8c',
                'mailers' => [
                    TestAMailer::class,
                ],
            ],
        ]);

        $component = LiveWire::actingAs($staff, 'staff')
            ->test(OrderStatus::class, [
                'order' => $order,
            ])->assertSet('statuses', config('lunar.orders.statuses'))
            ->set('newStatus', 'awaiting-payment')
            ->set('selectedMailers', [
                'test_a_mailer',
            ])
            ->set('emailAddresses', [
                'foo@bar.test',
            ])->call('updateStatus');

        $this->assertDatabaseHas(
            (new Order)->getTable(),
            [
                'id' => $order->id,
                'status' => $component->get('newStatus'),
            ]
        );
    }
}
