<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource\Widgets\ProductOptionsWidget;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.product.widgets');

it('can save a new size and colour permutation that has no variant_id or copied_id', function () {
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

    $this->asStaff(admin: true);

    Livewire::test(ProductOptionsWidget::class, [
        'record' => $product->fresh(),
    ])->set('variants', [
        [
            'key' => 'existing',
            'variant_id' => $variant->id,
            'copied_id' => null,
            'sku' => 'SMALL-RED',
            'price' => 15,
            'stock' => 4,
            'values' => [],
        ],
        [
            'key' => 'new',
            'variant_id' => null,
            'copied_id' => null,
            'sku' => 'LARGE-BLUE',
            'price' => 18,
            'stock' => 0,
            'values' => [],
        ],
    ])->callAction('saveVariants')
        ->assertHasNoErrors();

    $created = ProductVariant::query()
        ->where('product_id', $product->id)
        ->where('sku', 'LARGE-BLUE')
        ->first();

    expect($created)->not->toBeNull()
        ->and($created->tax_class_id)->toBe($variant->tax_class_id)
        ->and($created->stock)->toBe(0)
        ->and($created->basePrices->first()?->price->value)->toBe(1800);
});
