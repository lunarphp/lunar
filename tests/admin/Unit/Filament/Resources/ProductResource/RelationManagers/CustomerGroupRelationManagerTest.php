<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource\RelationManagers\CustomerGroupRelationManager;

uses(\Lunar\Tests\Admin\Unit\Filament\TestCase::class)
    ->group('resource.product');

it('can render relationship manager', function () {
    \Lunar\Models\CustomerGroup::factory()->create([
        'default' => true,
    ]);

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $product = \Lunar\Models\Product::factory()->create();

    $this->asStaff(admin: true);

    Livewire::test(CustomerGroupRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => 'customerGroupRelationManager',
    ])->assertSuccessful();
});
