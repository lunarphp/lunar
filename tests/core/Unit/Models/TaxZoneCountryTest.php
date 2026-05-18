<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Country;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneCountry;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can make tax zone country', function () {
    $data = [
        'tax_zone_id' => TaxZone::factory()->create()->id,
        'country_id' => Country::factory()->create()->id,
    ];

    TaxZoneCountry::factory()->create($data);

    $this->assertDatabaseHas((new TaxZoneCountry)->getTable(), $data);
});
