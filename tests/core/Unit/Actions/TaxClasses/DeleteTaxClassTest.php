<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\TaxClasses\DeleteTaxClass;
use Lunar\Core\Exceptions\TaxClassActionException;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxRateAmount;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes a tax class along with its rate amounts', function () {
    $taxClass = TaxClass::factory()->create(['default' => false]);
    $amount = TaxRateAmount::factory()->create(['tax_class_id' => $taxClass->id]);

    app(DeleteTaxClass::class)->execute($taxClass);

    $this->assertDatabaseMissing('lunar_tax_classes', ['id' => $taxClass->id]);
    $this->assertDatabaseMissing('lunar_tax_rate_amounts', ['id' => $amount->id]);
});

test('refuses to delete the default tax class', function () {
    $taxClass = TaxClass::factory()->create(['default' => true]);

    expect(fn () => app(DeleteTaxClass::class)->execute($taxClass))
        ->toThrow(TaxClassActionException::class);
});

test('refuses to delete a tax class with product variants', function () {
    $taxClass = TaxClass::factory()->create(['default' => false]);
    ProductVariant::factory()->create(['tax_class_id' => $taxClass->id]);

    expect(fn () => app(DeleteTaxClass::class)->execute($taxClass))
        ->toThrow(TaxClassActionException::class);

    $this->assertDatabaseHas('lunar_tax_classes', ['id' => $taxClass->id]);
});
