<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\TaxZones\CreateTaxZone;
use Lunar\Core\Models\TaxZone;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a tax zone with the given attributes', function () {
    $taxZone = app(CreateTaxZone::class)->execute([
        'name' => 'UK',
        'zone_type' => 'country',
        'active' => true,
        'default' => false,
    ]);

    expect($taxZone)->toBeInstanceOf(TaxZone::class);

    $this->assertDatabaseHas('lunar_tax_zones', [
        'id' => $taxZone->id,
        'name' => 'UK',
    ]);
});

test('the model hook keeps a single default tax zone', function () {
    $previous = TaxZone::factory()->create(['default' => true]);

    app(CreateTaxZone::class)->execute([
        'name' => 'UK',
        'zone_type' => 'country',
        'active' => true,
        'default' => true,
    ]);

    expect($previous->refresh()->default)->toBeFalse()
        ->and(TaxZone::query()->where('default', true)->count())->toBe(1);
});
