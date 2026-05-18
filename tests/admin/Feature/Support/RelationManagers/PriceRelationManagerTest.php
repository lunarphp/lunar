<?php

use Filament\Actions\EditAction;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductPricing;
use Lunar\Admin\Support\RelationManagers\PriceRelationManager;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('support.relation-managers');

it('can render relation manager', function ($model, $page) {
    $this->asStaff();

    Language::factory()->create([
        'default' => true,
    ]);

    $model = $model::factory()->create();

    Livewire::test(PriceRelationManager::class, [
        'ownerRecord' => $model,
        'pageClass' => $page,
    ])->assertSuccessful();
})->with([
    [Product::class, ManageProductPricing::class],
]);

it('can mount the edit action on a tier price without a Price object cast error', function () {
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
    ]);

    $price = Price::factory()->create([
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'min_quantity' => 5,
        'price' => 1099,
        'compare_price' => 1299,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(PriceRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => ManageProductPricing::class,
    ])
        ->mountTableAction(EditAction::class, $price)
        ->assertTableActionDataSet([
            'price' => 10.99,
            'compare_price' => 12.99,
            'min_quantity' => 5,
            'currency_id' => $currency->id,
        ])
        ->assertHasNoErrors();
});
