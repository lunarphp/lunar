<?php

use Livewire\Livewire;
use Lunar\Admin\Actions\Products\MapVariantsToProductOptions;
use Lunar\Admin\Filament\Resources\ProductResource\Widgets\ProductOptionsWidget;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.product.widgets');

it('can save newly filled size and colour permutations', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);

    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'SMALL-RED',
        'stock' => 4,
    ]);

    $variant->prices()->create([
        'price' => 1500,
        'currency_id' => $currency->id,
    ]);

    $mapped = collect(MapVariantsToProductOptions::map(
        [
            'Size' => [
                'Small',
                'Large',
            ],
            'Colour' => [
                'Red',
                'Blue',
            ],
        ],
        [
            [
                'id' => $variant->id,
                'sku' => 'SMALL-RED',
                'price' => 15,
                'stock' => 4,
                'values' => [
                    'Size' => 'Small',
                    'Colour' => 'Red',
                ],
            ],
        ],
    ))->map(function (array $row) {
        $row['sku'] = $row['sku'] ?: collect($row['values'])->join('-');
        $row['values'] = [];

        return $row;
    })->all();

    $this->asStaff(admin: true);

    Livewire::test(ProductOptionsWidget::class, [
        'record' => $product->fresh(),
    ])->set('variants', $mapped)
        ->callAction('saveVariants')
        ->assertHasNoErrors();

    $variants = ProductVariant::query()
        ->where('product_id', $product->id)
        ->get();

    expect($variants)->toHaveCount(4)
        ->and($variants->pluck('tax_class_id')->unique()->all())->toBe([$variant->tax_class_id])
        ->and($variants->pluck('sku'))->toContain('SMALL-RED');
});
