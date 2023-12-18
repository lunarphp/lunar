<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\Models\Country;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneCountry;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can make tax zone country', function () {
    $data = [
        'tax_zone_id' => TaxZone::factory()->create()->id,
        'country_id' => Country::factory()->create()->id,
    ];

    TaxZoneCountry::factory()->create($data);

    $this->assertDatabaseHas((new TaxZoneCountry())->getTable(), $data);
});
