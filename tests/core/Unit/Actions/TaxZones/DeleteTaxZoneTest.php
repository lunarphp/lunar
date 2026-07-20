<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\TaxZones\DeleteTaxZone;
use Lunar\Core\Exceptions\TaxZoneActionException;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\TaxZone;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes a tax zone along with its coverage', function () {
    $taxZone = TaxZone::factory()->create(['default' => false]);
    $taxZone->countries()->create(['country_id' => Country::factory()->create()->id]);

    app(DeleteTaxZone::class)->execute($taxZone);

    $this->assertDatabaseMissing('lunar_tax_zones', ['id' => $taxZone->id]);
    $this->assertDatabaseMissing('lunar_tax_zone_countries', ['tax_zone_id' => $taxZone->id]);
});

test('refuses to delete the default tax zone', function () {
    $taxZone = TaxZone::factory()->create(['default' => true]);

    expect(fn () => app(DeleteTaxZone::class)->execute($taxZone))
        ->toThrow(TaxZoneActionException::class);

    $this->assertDatabaseHas('lunar_tax_zones', ['id' => $taxZone->id]);
});
