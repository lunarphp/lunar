<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\ProductOptions\UpdateProductOption;
use Lunar\Core\Exceptions\ProductOptionActionException;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the product option attributes', function () {
    $option = ProductOption::factory()->create(['handle' => 'colour']);

    app(UpdateProductOption::class)->execute($option, ['handle' => 'shade']);

    $this->assertDatabaseHas('lunar_product_options', [
        'id' => $option->id,
        'handle' => 'shade',
    ]);
});

test('syncs values, updating rows with an id in place', function () {
    $option = ProductOption::factory()->create();
    $keep = ProductOptionValue::factory()->create(['product_option_id' => $option->id]);
    $stale = ProductOptionValue::factory()->create(['product_option_id' => $option->id]);

    app(UpdateProductOption::class)->execute($option, [
        'values' => [
            ['id' => $keep->id, 'name' => ['en' => 'Crimson'], 'position' => 1],
            ['name' => ['en' => 'Teal'], 'position' => 2],
        ],
    ]);

    $values = $option->values()->orderBy('position')->get();

    expect($values)->toHaveCount(2)
        ->and($values[0]->id)->toBe($keep->id)
        ->and($values[0]->translate('name'))->toBe('Crimson')
        ->and($values[1]->translate('name'))->toBe('Teal');

    $this->assertDatabaseMissing('lunar_product_option_values', ['id' => $stale->id]);
});

test('keeps the values when none are supplied', function () {
    $option = ProductOption::factory()->create();
    ProductOptionValue::factory()->create(['product_option_id' => $option->id]);

    app(UpdateProductOption::class)->execute($option, ['handle' => 'renamed']);

    expect($option->values()->count())->toBe(1);
});

test('refuses to remove a value carried by variants', function () {
    $option = ProductOption::factory()->create();
    $value = ProductOptionValue::factory()->create(['product_option_id' => $option->id]);
    $value->variants()->attach(ProductVariant::factory()->create());

    expect(fn () => app(UpdateProductOption::class)->execute($option, ['values' => []]))
        ->toThrow(ProductOptionActionException::class);

    $this->assertDatabaseHas('lunar_product_option_values', ['id' => $value->id]);
});
