<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Country;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZonePostcode;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can make tax zone postcode', function () {
    $data = [
        'tax_zone_id' => TaxZone::factory()->create()->id,
        'country_id' => Country::factory()->create()->id,
        'postcode' => 123456,
    ];

    TaxZonePostcode::factory()->create($data);

    $this->assertDatabaseHas((new TaxZonePostcode)->getTable(), $data);
});
