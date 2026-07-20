<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\ProductOptions\CreateProductOption;
use Lunar\Core\Models\ProductOption;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a product option with the given attributes', function () {
    $option = app(CreateProductOption::class)->execute([
        'name' => ['en' => 'Colour'],
        'label' => ['en' => 'Colour'],
        'handle' => 'colour',
    ]);

    expect($option)->toBeInstanceOf(ProductOption::class);

    $this->assertDatabaseHas('lunar_product_options', [
        'id' => $option->id,
        'handle' => 'colour',
    ]);
});
