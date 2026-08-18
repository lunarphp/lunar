<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\ProductOptions\DeleteProductOption;
use Lunar\Core\Exceptions\ProductOptionActionException;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes an unused product option along with its values', function () {
    $option = ProductOption::factory()->create();
    $value = ProductOptionValue::factory()->create(['product_option_id' => $option->id]);

    app(DeleteProductOption::class)->execute($option);

    $this->assertDatabaseMissing('lunar_product_options', ['id' => $option->id]);
    $this->assertDatabaseMissing('lunar_product_option_values', ['id' => $value->id]);
});

test('refuses to delete an option linked to products', function () {
    $option = ProductOption::factory()->create();
    $option->products()->attach(Product::factory()->create(), ['position' => 1]);

    expect(fn () => app(DeleteProductOption::class)->execute($option))
        ->toThrow(ProductOptionActionException::class);
});

test('refuses to delete an option whose values are carried by variants', function () {
    $option = ProductOption::factory()->create();
    $value = ProductOptionValue::factory()->create(['product_option_id' => $option->id]);
    $value->variants()->attach(ProductVariant::factory()->create());

    expect(fn () => app(DeleteProductOption::class)->execute($option))
        ->toThrow(ProductOptionActionException::class);

    $this->assertDatabaseHas('lunar_product_options', ['id' => $option->id]);
});
