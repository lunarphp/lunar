<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components;

use GetCandy\Hub\Http\Livewire\Components\ActivityLogFeed;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

/**
 * @group hub.activity-log
 */
class ActivityLogFeedTest extends TestCase
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

        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        Livewire::actingAs($staff, 'staff')->test(ActivityLogFeed::class, [
            'subject' => $order,
        ]);
    }

    /** @test */
    public function can_add_comment()
    {
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

        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        Livewire::actingAs($staff, 'staff')->test(ActivityLogFeed::class, [
            'subject' => $order,
        ])->set('comment', 'Testing 123')
            ->call('addComment')
            ->assertHasNoErrors()
            ->assertSet('comment', null)
            ->assertSee('Testing 123');

        $this->assertDatabaseHas((new Activity())->getTable(), [
            'event' => 'comment',
            'subject_id' => $order->id,
            'subject_type' => Order::class,
            'causer_id' => $staff->id,
            'properties' => json_encode([
                'content' => 'Testing 123',
            ]),
        ]);
    }
}
