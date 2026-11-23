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

test('stores a value colour in the colour column, leaving meta alone', function () {
    $option = ProductOption::factory()->colour()->create();
    $value = ProductOptionValue::factory()->create([
        'product_option_id' => $option->id,
        'meta' => ['colour' => 'store-value'],
    ]);

    app(UpdateProductOption::class)->execute($option, [
        'values' => [
            ['id' => $value->id, 'name' => ['en' => 'Navy'], 'position' => 1, 'colour' => '#1f2a44'],
            ['name' => ['en' => 'Sky'], 'position' => 2, 'colour' => '#4aD'],
        ],
    ]);

    $values = $option->values()->orderBy('position')->get();

    expect($values[0]->colour)->toBe('#1F2A44')
        ->and($values[0]->meta['colour'])->toBe('store-value')
        ->and($values[1]->colour)->toBe('#44AADD');
});

test('rejects a colour that is not a hex colour', function (string $colour) {
    $option = ProductOption::factory()->colour()->create();

    expect(fn () => app(UpdateProductOption::class)->execute($option, [
        'values' => [
            ['name' => ['en' => 'Navy'], 'position' => 1, 'colour' => $colour],
        ],
    ]))->toThrow(ProductOptionActionException::class, "\"{$colour}\" is not a valid colour");

    expect($option->values()->count())->toBe(0);
})->with(['1F2A44', '#1F2A4', '#GGGGGG', 'navy']);

test('ignores a colour on an option that is not a colour option', function () {
    $option = ProductOption::factory()->create(['type' => 'text']);

    app(UpdateProductOption::class)->execute($option, [
        'values' => [
            ['name' => ['en' => 'Navy'], 'position' => 1, 'colour' => '#1F2A44'],
        ],
    ]);

    expect($option->values()->first()->colour)->toBeNull();
});

test('changing the type clears the value colour payload', function () {
    $option = ProductOption::factory()->colour()->create();
    $value = ProductOptionValue::factory()->colour('#1F2A44')->create(['product_option_id' => $option->id]);

    app(UpdateProductOption::class)->execute($option, ['type' => 'text']);

    expect($option->fresh()->type)->toBe('text')
        ->and($value->fresh()->colour)->toBeNull();
});

test('refuses to remove a value carried by variants', function () {
    $option = ProductOption::factory()->create();
    $value = ProductOptionValue::factory()->create(['product_option_id' => $option->id]);
    $value->variants()->attach(ProductVariant::factory()->create());

    expect(fn () => app(UpdateProductOption::class)->execute($option, ['values' => []]))
        ->toThrow(ProductOptionActionException::class);

    $this->assertDatabaseHas('lunar_product_option_values', ['id' => $value->id]);
});
