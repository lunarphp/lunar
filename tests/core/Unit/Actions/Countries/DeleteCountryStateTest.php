<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Countries\DeleteCountryState;
use Lunar\Core\Exceptions\CountryActionException;
use Lunar\Core\Models\State;
use Lunar\Core\Models\TaxZoneState;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes an unreferenced state', function () {
    $state = State::factory()->create();

    app(DeleteCountryState::class)->execute($state);

    $this->assertDatabaseMissing('lunar_states', ['id' => $state->id]);
});

test('refuses to delete a state referenced by a tax zone', function () {
    $state = State::factory()->create();
    TaxZoneState::factory()->create(['state_id' => $state->id]);

    expect(fn () => app(DeleteCountryState::class)->execute($state))
        ->toThrow(CountryActionException::class);

    $this->assertDatabaseHas('lunar_states', ['id' => $state->id]);
});
