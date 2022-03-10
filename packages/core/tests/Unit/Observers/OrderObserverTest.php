<?php

namespace GetCandy\Tests\Unit\Observers;

use GetCandy\Models\Language;
use GetCandy\Models\Order;
use GetCandy\Models\Url;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

/**
 * @group observers
 */
class OrderObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function activity_is_logged_when_status_changes()
    {
        $order = Order::factory()->create([
            'status' => 'status-a',
        ]);

        $this->assertDatabaseMissing((new Activity)->getTable(), [
            'subject_id' => $order->id,
            'event' => 'status-update'
        ]);
    }
}
