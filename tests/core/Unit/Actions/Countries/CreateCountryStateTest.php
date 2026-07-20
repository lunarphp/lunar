<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Countries\CreateCountryState;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\State;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a state on the country', function () {
    $country = Country::factory()->create();

    $state = app(CreateCountryState::class)->execute($country, [
        'name' => 'Yorkshire',
        'code' => 'YRK',
    ]);

    expect($state)->toBeInstanceOf(State::class);

    $this->assertDatabaseHas('lunar_states', [
        'id' => $state->id,
        'country_id' => $country->id,
        'name' => 'Yorkshire',
        'code' => 'YRK',
    ]);
});
