<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Spatie\Activitylog\Models\Activity;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

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

test('activity is logged when status changes', function () {
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
});
