<?php

use Filament\Actions\EditAction;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource\RelationManagers\CustomerGroupPricingRelationManager;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.product');

it('can mount the edit action on a customer group price without a Price object cast error', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $price = Price::factory()->create([
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => $currency->id,
        'customer_group_id' => $customerGroup->id,
        'min_quantity' => 1,
        'price' => 1099,
        'compare_price' => 1299,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(CustomerGroupPricingRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => 'productEdit',
    ])
        ->mountTableAction(EditAction::class, $price)
        ->assertTableActionDataSet([
            'price' => 10.99,
            'compare_price' => 12.99,
            'customer_group_id' => $customerGroup->id,
            'currency_id' => $currency->id,
        ])
        ->assertHasNoErrors();
});
