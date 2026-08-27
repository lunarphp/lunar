<?php

use Lunar\Filament\Schemas\ProductVariant\ProductVariantForm;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

it('requires a unit quantity of at least one', function () {
    // A unit quantity of zero divides by zero in the price formatter.
    expect(ProductVariantForm::getUnitQtyComponent()->getValidationRules())
        ->toContain('min:1');
});
