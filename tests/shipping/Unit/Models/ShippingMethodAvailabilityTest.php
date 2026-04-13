<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Tests\Shipping\TestCase;

uses(RefreshDatabase::class);
uses(TestCase::class);

// Schedule is stored as data.schedule keyed by ISO weekday (1=Monday … 7=Sunday).
// Each entry: { enabled: bool, from: "HH:MM", to: "HH:MM" }

// ── No schedule ──────────────────────────────────────────────────────────────

test('is available when no schedule is set', function () {
    $method = ShippingMethod::factory()->create(['data' => []]);

    expect($method->isAvailable(Carbon::now()))->toBeTrue();
});

// ── Day-level availability ───────────────────────────────────────────────────

test('is available on an enabled day', function () {
    $monday = Carbon::parse('next Monday')->setTime(12, 0);

    $method = ShippingMethod::factory()->create([
        'data' => ['schedule' => [
            '1' => ['enabled' => true],
        ]],
    ]);

    expect($method->isAvailable($monday))->toBeTrue();
});

test('is not available on a day that is not enabled', function () {
    $tuesday = Carbon::parse('next Tuesday')->setTime(12, 0);

    $method = ShippingMethod::factory()->create([
        'data' => ['schedule' => [
            '1' => ['enabled' => true],
            '2' => ['enabled' => false],
        ]],
    ]);

    expect($method->isAvailable($tuesday))->toBeFalse();
});

test('is not available on a day absent from the schedule', function () {
    $wednesday = Carbon::parse('next Wednesday')->setTime(12, 0);

    $method = ShippingMethod::factory()->create([
        'data' => ['schedule' => [
            '1' => ['enabled' => true],
        ]],
    ]);

    expect($method->isAvailable($wednesday))->toBeFalse();
});

// ── Per-day time windows ─────────────────────────────────────────────────────

test('is available when current time is within the day\'s from/to window', function () {
    $monday = Carbon::parse('next Monday')->setTime(13, 0);

    $method = ShippingMethod::factory()->create([
        'data' => ['schedule' => [
            '1' => ['enabled' => true, 'from' => '10:00', 'to' => '17:00'],
        ]],
    ]);

    expect($method->isAvailable($monday))->toBeTrue();
});

test('is not available when current time is before the day\'s from time', function () {
    $monday = Carbon::parse('next Monday')->setTime(9, 30);

    $method = ShippingMethod::factory()->create([
        'data' => ['schedule' => [
            '1' => ['enabled' => true, 'from' => '10:00', 'to' => '17:00'],
        ]],
    ]);

    expect($method->isAvailable($monday))->toBeFalse();
});

test('is not available when current time is after the day\'s to time', function () {
    $monday = Carbon::parse('next Monday')->setTime(17, 1);

    $method = ShippingMethod::factory()->create([
        'data' => ['schedule' => [
            '1' => ['enabled' => true, 'from' => '10:00', 'to' => '17:00'],
        ]],
    ]);

    expect($method->isAvailable($monday))->toBeFalse();
});

test('is available exactly at the from time', function () {
    $monday = Carbon::parse('next Monday')->setTime(10, 0, 0);

    $method = ShippingMethod::factory()->create([
        'data' => ['schedule' => [
            '1' => ['enabled' => true, 'from' => '10:00', 'to' => '17:00'],
        ]],
    ]);

    expect($method->isAvailable($monday))->toBeTrue();
});

test('is available exactly at the to time', function () {
    $monday = Carbon::parse('next Monday')->setTime(17, 0, 0);

    $method = ShippingMethod::factory()->create([
        'data' => ['schedule' => [
            '1' => ['enabled' => true, 'from' => '10:00', 'to' => '17:00'],
        ]],
    ]);

    expect($method->isAvailable($monday))->toBeTrue();
});

// ── Different windows per day ─────────────────────────────────────────────────

test('each day can have its own time window', function () {
    $method = ShippingMethod::factory()->create([
        'data' => ['schedule' => [
            '1' => ['enabled' => true, 'from' => '09:00', 'to' => '17:00'], // Mon 9–17
            '6' => ['enabled' => true, 'from' => '10:00', 'to' => '13:00'], // Sat 10–13
        ]],
    ]);

    $mondayMorning = Carbon::parse('next Monday')->setTime(9, 30);
    $saturdayMorning = Carbon::parse('next Saturday')->setTime(11, 0);
    $saturdayAfternoon = Carbon::parse('next Saturday')->setTime(14, 0);

    expect($method->isAvailable($mondayMorning))->toBeTrue();
    expect($method->isAvailable($saturdayMorning))->toBeTrue();
    expect($method->isAvailable($saturdayAfternoon))->toBeFalse();
});

test('an enabled day with no from/to is available all day', function () {
    $method = ShippingMethod::factory()->create([
        'data' => ['schedule' => [
            '3' => ['enabled' => true], // Wednesday, all day
        ]],
    ]);

    $earlyWednesday = Carbon::parse('next Wednesday')->setTime(0, 1);
    $lateWednesday = Carbon::parse('next Wednesday')->setTime(23, 59);

    expect($method->isAvailable($earlyWednesday))->toBeTrue();
    expect($method->isAvailable($lateWednesday))->toBeTrue();
});
