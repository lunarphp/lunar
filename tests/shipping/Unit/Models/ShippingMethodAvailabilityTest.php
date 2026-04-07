<?php

uses(\Lunar\Tests\Shipping\TestCase::class);

use Carbon\Carbon;
use Lunar\Shipping\Models\ShippingMethod;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ── Schedule-based availability ──────────────────────────────────────────────

test('is available when no schedule and no cutoff are set', function () {
    $method = ShippingMethod::factory()->create(['cutoff' => null, 'data' => []]);

    expect($method->isAvailableAt(Carbon::now()))->toBeTrue();
});

test('is available when schedule days matches the current day', function () {
    $monday = Carbon::parse('next Monday')->setTime(12, 0);

    $method = ShippingMethod::factory()->create([
        'cutoff' => null,
        'data' => ['schedule' => ['days' => [1, 2, 3, 4, 5]]],
    ]);

    expect($method->isAvailableAt($monday))->toBeTrue();
});

test('is not available when schedule days does not include the current day', function () {
    $saturday = Carbon::parse('next Saturday')->setTime(12, 0);

    $method = ShippingMethod::factory()->create([
        'cutoff' => null,
        'data' => ['schedule' => ['days' => [1, 2, 3, 4, 5]]],
    ]);

    expect($method->isAvailableAt($saturday))->toBeFalse();
});

test('is available on any day when schedule days is empty', function () {
    $sunday = Carbon::parse('next Sunday')->setTime(12, 0);

    $method = ShippingMethod::factory()->create([
        'cutoff' => null,
        'data' => ['schedule' => ['days' => []]],
    ]);

    expect($method->isAvailableAt($sunday))->toBeTrue();
});

test('is available when current time is within the from/to window', function () {
    $now = Carbon::parse('next Monday')->setTime(13, 0);

    $method = ShippingMethod::factory()->create([
        'cutoff' => null,
        'data' => ['schedule' => ['from' => '10:00', 'to' => '17:00']],
    ]);

    expect($method->isAvailableAt($now))->toBeTrue();
});

test('is not available when current time is before the from time', function () {
    $now = Carbon::parse('next Monday')->setTime(9, 30);

    $method = ShippingMethod::factory()->create([
        'cutoff' => null,
        'data' => ['schedule' => ['from' => '10:00', 'to' => '17:00']],
    ]);

    expect($method->isAvailableAt($now))->toBeFalse();
});

test('is not available when current time is after the to time', function () {
    $now = Carbon::parse('next Monday')->setTime(17, 1);

    $method = ShippingMethod::factory()->create([
        'cutoff' => null,
        'data' => ['schedule' => ['from' => '10:00', 'to' => '17:00']],
    ]);

    expect($method->isAvailableAt($now))->toBeFalse();
});

test('is available exactly at the from time', function () {
    $now = Carbon::parse('next Monday')->setTime(10, 0, 0);

    $method = ShippingMethod::factory()->create([
        'cutoff' => null,
        'data' => ['schedule' => ['from' => '10:00', 'to' => '17:00']],
    ]);

    expect($method->isAvailableAt($now))->toBeTrue();
});

test('is available exactly at the to time', function () {
    $now = Carbon::parse('next Monday')->setTime(17, 0, 0);

    $method = ShippingMethod::factory()->create([
        'cutoff' => null,
        'data' => ['schedule' => ['from' => '10:00', 'to' => '17:00']],
    ]);

    expect($method->isAvailableAt($now))->toBeTrue();
});

test('combines day and time checks — rejects wrong day even within time window', function () {
    $saturday = Carbon::parse('next Saturday')->setTime(12, 0);

    $method = ShippingMethod::factory()->create([
        'cutoff' => null,
        'data' => ['schedule' => ['days' => [1, 2, 3, 4, 5], 'from' => '10:00', 'to' => '17:00']],
    ]);

    expect($method->isAvailableAt($saturday))->toBeFalse();
});

test('combines day and time checks — rejects correct day but outside time window', function () {
    $mondayEvening = Carbon::parse('next Monday')->setTime(18, 0);

    $method = ShippingMethod::factory()->create([
        'cutoff' => null,
        'data' => ['schedule' => ['days' => [1, 2, 3, 4, 5], 'from' => '10:00', 'to' => '17:00']],
    ]);

    expect($method->isAvailableAt($mondayEvening))->toBeFalse();
});

test('is available when only a from time is set and current time is after it', function () {
    $now = Carbon::parse('next Monday')->setTime(11, 0);

    $method = ShippingMethod::factory()->create([
        'cutoff' => null,
        'data' => ['schedule' => ['from' => '10:00']],
    ]);

    expect($method->isAvailableAt($now))->toBeTrue();
});

test('is available when only a to time is set and current time is before it', function () {
    $now = Carbon::parse('next Monday')->setTime(14, 0);

    $method = ShippingMethod::factory()->create([
        'cutoff' => null,
        'data' => ['schedule' => ['to' => '17:00']],
    ]);

    expect($method->isAvailableAt($now))->toBeTrue();
});

// ── Legacy cutoff fallback ───────────────────────────────────────────────────

test('falls back to cutoff column when no schedule is set and cutoff has not passed', function () {
    $now = Carbon::parse('next Monday')->setTime(14, 0);

    $method = ShippingMethod::factory()->create([
        'cutoff' => '17:00:00',
        'data' => [],
    ]);

    expect($method->isAvailableAt($now))->toBeTrue();
});

test('falls back to cutoff column when no schedule is set and cutoff has passed', function () {
    $now = Carbon::parse('next Monday')->setTime(18, 0);

    $method = ShippingMethod::factory()->create([
        'cutoff' => '17:00:00',
        'data' => [],
    ]);

    expect($method->isAvailableAt($now))->toBeFalse();
});

test('schedule takes priority over the legacy cutoff column', function () {
    // Schedule says available, cutoff would say not available
    $now = Carbon::parse('next Monday')->setTime(14, 0);

    $method = ShippingMethod::factory()->create([
        'cutoff' => '10:00:00', // would reject at 14:00
        'data' => ['schedule' => ['from' => '09:00', 'to' => '18:00']],
    ]);

    expect($method->isAvailableAt($now))->toBeTrue();
});
