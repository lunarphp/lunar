<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Countries\UpdateCountry;
use Lunar\Core\Models\Country;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the country attributes', function () {
    $country = Country::factory()->create(['name' => 'Old Name']);

    app(UpdateCountry::class)->execute($country, ['name' => 'New Name']);

    $this->assertDatabaseHas('lunar_countries', [
        'id' => $country->id,
        'name' => 'New Name',
    ]);
});
