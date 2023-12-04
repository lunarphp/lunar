<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Actions\Taxes\GetTaxZoneState;
use Lunar\Models\State;
use Lunar\Models\TaxZoneState;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can match exact state name', function () {
    $california = State::factory()->create([
        'code' => 'CA',
        'name' => 'California',
    ]);

    $alabama = State::factory()->create([
        'code' => 'AL',
        'name' => 'Alabama',
    ]);

    TaxZoneState::factory()->create([
        'state_id' => $california->id,
    ]);

    $al = TaxZoneState::factory()->create([
        'state_id' => $alabama->id,
    ]);

    $zone = app(GetTaxZoneState::class)->execute('Alabama');

    expect($zone->id)->toEqual($al->id);
});

test('can match exact state code', function () {
    $california = State::factory()->create([
        'code' => 'CA',
        'name' => 'California',
    ]);

    $alabama = State::factory()->create([
        'code' => 'AL',
        'name' => 'Alabama',
    ]);

    TaxZoneState::factory()->create([
        'state_id' => $california->id,
    ]);

    $al = TaxZoneState::factory()->create([
        'state_id' => $alabama->id,
    ]);

    $zone = app(GetTaxZoneState::class)->execute('AL');

    expect($zone)->not->toBeNull();

    expect($zone?->id)->toEqual($al->id);
});

test('can mismatch exact state name', function () {
    $california = State::factory()->create([
        'code' => 'CA',
        'name' => 'California',
    ]);

    $alabama = State::factory()->create([
        'code' => 'AL',
        'name' => 'Alabama',
    ]);

    TaxZoneState::factory()->create([
        'state_id' => $california->id,
    ]);

    $al = TaxZoneState::factory()->create([
        'state_id' => $alabama->id,
    ]);

    $zone = app(GetTaxZoneState::class)->execute('Alaba');

    expect($zone)->toBeNull();

    $this->assertNotEquals($al->id, $zone?->id);
});
