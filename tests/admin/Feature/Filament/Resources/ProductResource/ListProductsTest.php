<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ListProducts;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

use function Pest\Laravel\assertDatabaseHas;

uses(TestCase::class)
    ->group('resource.product');

it('can create product', function () {
    TaxClass::factory()->create([
        'default' => true,
    ]);
    Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);
    $language = Language::factory()->create([
        'default' => true,
    ]);

    $productType = ProductType::factory()->create();

    $this->asStaff();

    Livewire::test(ListProducts::class)
        ->callAction('create', data: [
            'name' => [$language->code => 'Foo Bar'],
            'base_price' => 10.99,
            'sku' => 'ABCABCAB',
            'product_type_id' => $productType->id,
        ])->assertHasNoActionErrors();

    assertDatabaseHas((new Product)->getTable(), [
        'product_type_id' => $productType->id,
        'status' => 'draft',
        'name' => json_encode([
            $language->code => 'Foo Bar',
        ]),
    ]);

    $this->assertDatabaseHas((new ProductVariant)->getTable(), [
        'sku' => 'ABCABCAB',
    ]);

    $this->assertDatabaseHas((new Price)->getTable(), [
        'price' => '1099',
    ]);
});
