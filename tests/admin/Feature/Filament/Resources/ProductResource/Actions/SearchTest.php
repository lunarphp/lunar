<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ListProducts;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.product.search');

it('can search product by name on list', function () {

    $this->asStaff(admin: true);

    Language::factory()->create([
        'default' => true,
    ]);

    Currency::factory()->create([
        'default' => true,
    ]);

    $products = Product::factory()->count(2)->create();

    $products->each(function ($product) {
        ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);
    });

    $name = $products->first()->translateAttribute('name');

    $products = $products->filter(function ($item, $key) use ($name) {
        return $name == $item->translateAttribute('name');
    });

    Livewire::test(ListProducts::class)
        ->searchTable($name)
        ->assertCanNotSeeTableRecords($products);
});
