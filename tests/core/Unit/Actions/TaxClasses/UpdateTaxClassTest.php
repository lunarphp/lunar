<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\TaxClasses\UpdateTaxClass;
use Lunar\Core\Exceptions\TaxClassActionException;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the tax class attributes', function () {
    $taxClass = TaxClass::factory()->create(['name' => 'Old Name', 'default' => false]);

    app(UpdateTaxClass::class)->execute($taxClass, ['name' => 'New Name']);

    $this->assertDatabaseHas('lunar_tax_classes', [
        'id' => $taxClass->id,
        'name' => 'New Name',
    ]);
});

test('promoting to default demotes the previous default', function () {
    $previous = TaxClass::factory()->create(['default' => true]);
    $taxClass = TaxClass::factory()->create(['default' => false]);

    app(UpdateTaxClass::class)->execute($taxClass, ['default' => true]);

    expect($previous->refresh()->default)->toBeFalse()
        ->and($taxClass->refresh()->default)->toBeTrue();
});

test('refuses to unset the default flag directly', function () {
    $taxClass = TaxClass::factory()->create(['default' => true]);

    expect(fn () => app(UpdateTaxClass::class)->execute($taxClass, ['default' => false]))
        ->toThrow(TaxClassActionException::class);
});
