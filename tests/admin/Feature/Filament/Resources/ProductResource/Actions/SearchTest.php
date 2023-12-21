<?php

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.product.search');

it('can search product by name on list', function () {

    $this->asStaff(admin: true);

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    \Lunar\Models\Currency::factory()->create([
        'default' => true,
    ]);

    $products = \Lunar\Models\Product::factory()->count(2)->create();

    $products->each(function ($product) {
        \Lunar\Models\ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);
    });

    $name = $products->first()->translateAttribute('name');

    $products = $products->filter(function ($item, $key) use ($name) {
        return $name == $item->translateAttribute('name');
    });

    \Livewire\Livewire::test(Lunar\Admin\Filament\Resources\ProductResource\Pages\ListProducts::class)
        ->searchTable($name)
        ->assertCanNotSeeTableRecords($products);
});