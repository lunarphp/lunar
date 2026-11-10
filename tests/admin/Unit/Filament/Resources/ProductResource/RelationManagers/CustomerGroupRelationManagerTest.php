<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource\RelationManagers\CustomerGroupRelationManager;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Tests\Admin\Unit\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.product');

it('can render relationship manager', function () {
    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    Language::factory()->create([
        'default' => true,
    ]);

    $product = Product::factory()->create();

    $this->asStaff(admin: true);

    Livewire::test(CustomerGroupRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => 'customerGroupRelationManager',
    ])->assertSuccessful();
});

it('labels the default customer group in the table', function () {
    $default = CustomerGroup::factory()->create([
        'name' => 'Retail',
        'default' => true,
    ]);

    CustomerGroup::factory()->create([
        'name' => 'Wholesale',
        'default' => false,
    ]);

    Language::factory()->create(['default' => true]);

    $product = Product::factory()->create();

    $this->asStaff(admin: true);

    Livewire::test(CustomerGroupRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => 'customerGroupRelationManager',
    ])
        ->assertSuccessful()
        ->assertSee(__('lunarpanel::relationmanagers.customer_groups.table.name.default_description'));
});
