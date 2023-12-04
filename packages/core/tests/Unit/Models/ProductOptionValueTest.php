<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can make a product option value with translations', function () {
    $optionValue = ProductOptionValue::factory()
        ->for(ProductOption::factory(), 'option')
        ->create();

    $this->assertDatabaseHas((new ProductOptionValue)->getTable(), [
        'id' => $optionValue->id,
        'product_option_id' => $optionValue->option->id,
        'name' => json_encode($optionValue->name),
    ]);

    $this->assertDatabaseCount((new ProductOptionValue)->getTable(), 1);
});

test('can edit translated product option value', function () {
    /** @var ProductOptionValue $optionValue */
    $optionValue = ProductOptionValue::factory()
        ->for(ProductOption::factory(), 'option')
        ->create();

    $optionValue->update(['name' => $updatedName = collect([
        'en' => $optionValue->translate('name').'-edited',
    ])]);

    $this->assertDatabaseHas((new ProductOptionValue)->getTable(), [
        'id' => $optionValue->id,
        'product_option_id' => $optionValue->option->id,
        'name' => $updatedName->toJson(),
    ]);
});

test('can delete product option value', function () {
    /** @var ProductOptionValue $optionValue */
    $optionValue = ProductOptionValue::factory()
        ->for(ProductOption::factory(), 'option')
        ->create();

    $optionValue->delete();

    $this->assertDatabaseMissing((new ProductOptionValue)->getTable(), [
        'id' => $optionValue->id,
    ]);
});

test('can update all product option value positions', function () {
    $optionValues = ProductOptionValue::factory(10)
        ->for(ProductOption::factory(), 'option')
        ->create()
        ->each(function ($optionValue) {
            $optionValue->update([
                'position' => $optionValue->id,
            ]);
        });

    $this->assertDatabaseCount((new ProductOptionValue)->getTable(), 10);
    expect($optionValues->pluck('position')->toArray())->toEqual(range(1, 10));
});
