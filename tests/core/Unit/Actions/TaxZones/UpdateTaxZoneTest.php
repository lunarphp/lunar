<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\TaxZones\UpdateTaxZone;
use Lunar\Core\Exceptions\TaxZoneActionException;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\State;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxRate;
use Lunar\Core\Models\TaxZone;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the tax zone attributes', function () {
    $taxZone = TaxZone::factory()->create(['name' => 'Old Name', 'default' => false]);

    app(UpdateTaxZone::class)->execute($taxZone, ['name' => 'New Name']);

    $this->assertDatabaseHas('lunar_tax_zones', [
        'id' => $taxZone->id,
        'name' => 'New Name',
    ]);
});

test('refuses to unset the default flag directly', function () {
    $taxZone = TaxZone::factory()->create(['default' => true]);

    expect(fn () => app(UpdateTaxZone::class)->execute($taxZone, ['default' => false]))
        ->toThrow(TaxZoneActionException::class);
});

test('replaces the country coverage when supplied', function () {
    $taxZone = TaxZone::factory()->create();
    $original = Country::factory()->create();
    $taxZone->countries()->create(['country_id' => $original->id]);

    $replacement = Country::factory()->create();

    app(UpdateTaxZone::class)->execute($taxZone, ['countries' => [$replacement->id]]);

    expect($taxZone->countries()->pluck('country_id')->all())->toBe([$replacement->id]);
});

test('replaces the state coverage when supplied', function () {
    $taxZone = TaxZone::factory()->create();
    $original = State::factory()->create();
    $taxZone->states()->create(['state_id' => $original->id]);

    $replacement = State::factory()->create();

    app(UpdateTaxZone::class)->execute($taxZone, ['states' => [$replacement->id]]);

    expect($taxZone->states()->pluck('state_id')->all())->toBe([$replacement->id]);
});

test('replaces the postcode coverage when supplied, keeping existing rows', function () {
    $taxZone = TaxZone::factory()->create();
    $country = Country::factory()->create();
    $kept = $taxZone->postcodes()->create(['country_id' => $country->id, 'postcode' => 'LS1']);
    $taxZone->postcodes()->create(['country_id' => $country->id, 'postcode' => 'LS2']);

    app(UpdateTaxZone::class)->execute($taxZone, [
        'postcodes' => [
            ['country_id' => $country->id, 'postcode' => 'LS1'],
            ['country_id' => $country->id, 'postcode' => 'LS3'],
        ],
    ]);

    expect($taxZone->postcodes()->pluck('postcode')->sort()->values()->all())->toBe(['LS1', 'LS3'])
        ->and($taxZone->postcodes()->where('postcode', 'LS1')->sole()->id)->toBe($kept->id);
});

test('replaces the customer group limits when supplied', function () {
    $taxZone = TaxZone::factory()->create();
    $original = CustomerGroup::factory()->create();
    $taxZone->customerGroups()->create(['customer_group_id' => $original->id]);

    $replacement = CustomerGroup::factory()->create();

    app(UpdateTaxZone::class)->execute($taxZone, ['customer_groups' => [$replacement->id]]);

    expect($taxZone->customerGroups()->pluck('customer_group_id')->all())->toBe([$replacement->id]);
});

test('syncs rates and their amounts, updating rows with an id in place', function () {
    $taxZone = TaxZone::factory()->create();
    $taxClass = TaxClass::factory()->create();
    $keep = TaxRate::factory()->create(['tax_zone_id' => $taxZone->id]);
    $stale = TaxRate::factory()->create(['tax_zone_id' => $taxZone->id]);

    app(UpdateTaxZone::class)->execute($taxZone, [
        'rates' => [
            ['id' => $keep->id, 'name' => 'VAT', 'priority' => 1, 'amounts' => [$taxClass->id => 20]],
            ['name' => 'Levy', 'priority' => 2, 'amounts' => [$taxClass->id => 2.5]],
        ],
    ]);

    $rates = $taxZone->taxRates()->orderBy('priority')->get();

    expect($rates)->toHaveCount(2)
        ->and($rates[0]->id)->toBe($keep->id)
        ->and($rates[0]->name)->toBe('VAT')
        ->and((float) $rates[0]->taxRateAmounts()->sole()->percentage)->toBe(20.0);

    $this->assertDatabaseMissing('lunar_tax_rates', ['id' => $stale->id]);
});
