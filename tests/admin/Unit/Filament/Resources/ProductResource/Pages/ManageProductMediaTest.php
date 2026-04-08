<?php

use Filament\Resources\RelationManagers\RelationGroup;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductMedia;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Tests\Admin\Unit\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.product');

it('can return configured relation managers', function () {
    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    Language::factory()->create([
        'default' => true,
    ]);

    $product = Product::factory()->create();

    $this->asStaff(admin: true);

    $component = Livewire::test(ManageProductMedia::class, [
        'record' => $product->id,
        'pageClass' => 'productMediaRelationManager',
    ])->assertSuccessful();

    $managers = $component->instance()->getRelationManagers();

    expect($managers[0])->toBeInstanceOf(RelationGroup::class);

    expect($managers[0]->getManagers())->toHaveCount(1);
});
