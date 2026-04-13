<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\TaxClass;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can make a tax class', function () {
    TaxClass::factory()->create([
        'name' => 'Clothing',
    ]);

    $this->assertDatabaseHas((new TaxClass)->getTable(), [
        'name' => 'Clothing',
        'default' => false,
    ]);
});

test('can get default tax class', function () {
    $taxClassA = TaxClass::factory()->create([
        'name' => 'Tax Class A',
        'default' => false,
    ]);

    $taxClassB = TaxClass::factory()->create([
        'name' => 'Tax Class B',
        'default' => true,
    ]);

    expect(TaxClass::getDefault()->id)->toEqual($taxClassB->id);
});
