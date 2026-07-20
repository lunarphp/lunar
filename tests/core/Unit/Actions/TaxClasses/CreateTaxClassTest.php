<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\TaxClasses\CreateTaxClass;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a tax class with the given attributes', function () {
    $taxClass = app(CreateTaxClass::class)->execute(['name' => 'Reduced rate']);

    expect($taxClass)->toBeInstanceOf(TaxClass::class);

    $this->assertDatabaseHas('lunar_tax_classes', [
        'id' => $taxClass->id,
        'name' => 'Reduced rate',
    ]);
});

test('the model hook keeps a single default tax class', function () {
    $previous = TaxClass::factory()->create(['default' => true]);

    app(CreateTaxClass::class)->execute(['name' => 'Reduced rate', 'default' => true]);

    expect($previous->refresh()->default)->toBeFalse()
        ->and(TaxClass::query()->where('default', true)->count())->toBe(1);
});
