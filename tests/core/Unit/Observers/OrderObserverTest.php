<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Order\Cancelled;
use Lunar\Core\States\Order\Order\OnHold;
use Lunar\Tests\Core\TestCase;
use Spatie\Activitylog\Models\Activity;

uses(TestCase::class);

uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);
});

test('activity is logged when order_status changes', function () {
    activity()->enableLogging();

    $order = Order::factory()->create();

    $this->assertDatabaseMissing((new Activity)->getTable(), [
        'subject_id' => $order->id,
        'event' => 'status-update',
    ]);

    $order->forceFill(['order_status' => OnHold::$name])->save();

    $this->assertDatabaseHas((new Activity)->getTable(), [
        'subject_id' => $order->id,
        'event' => 'status-update',
        'properties' => json_encode([
            'new' => 'on-hold',
            'previous' => 'awaiting-payment',
        ]),
    ]);

    $order->forceFill(['order_status' => OnHold::$name])->save();

    $this->assertDatabaseMissing((new Activity)->getTable(), [
        'subject_id' => $order->id,
        'event' => 'status-update',
        'properties' => json_encode([
            'new' => 'on-hold',
            'previous' => 'on-hold',
        ]),
    ]);

    $order->forceFill(['order_status' => Cancelled::$name])->save();

    $this->assertDatabaseHas((new Activity)->getTable(), [
        'subject_id' => $order->id,
        'event' => 'status-update',
        'properties' => json_encode([
            'new' => 'cancelled',
            'previous' => 'on-hold',
        ]),
    ]);
});
