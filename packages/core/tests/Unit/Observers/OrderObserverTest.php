<?php

namespace GetCandy\Tests\Unit\Observers;

use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Order;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

/**
 * @group observers
 */
class OrderObserverTest extends TestCase
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
    public function activity_is_logged_when_status_changes()
    {
        activity()->enableLogging();
        $order = Order::factory()->create([
            'status' => 'status-a',
        ]);

        $this->assertDatabaseMissing((new Activity)->getTable(), [
            'subject_id' => $order->id,
            'event' => 'status-update',
        ]);

        $order->update([
            'status' => 'status-b',
        ]);

        $this->assertDatabaseHas((new Activity)->getTable(), [
            'subject_id' => $order->id,
            'event' => 'status-update',
            'properties' => json_encode([
                'new' => 'status-b',
                'previous' => 'status-a',
            ]),
        ]);

        $order->update([
            'status' => 'status-b',
        ]);

        $this->assertDatabaseMissing((new Activity)->getTable(), [
            'subject_id' => $order->id,
            'event' => 'status-update',
            'properties' => json_encode([
                'new' => 'status-b',
                'previous' => 'status-b',
            ]),
        ]);

        $order->status = 'status-c';
        $order->save();

        $this->assertDatabaseHas((new Activity)->getTable(), [
            'subject_id' => $order->id,
            'event' => 'status-update',
            'properties' => json_encode([
                'new' => 'status-c',
                'previous' => 'status-b',
            ]),
        ]);
    }
}
