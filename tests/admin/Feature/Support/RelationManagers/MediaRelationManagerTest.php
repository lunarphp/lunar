<?php

use Livewire\Livewire;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('support.relation-managers');

it('can render relation manager', function ($model, $page) {
    $this->asStaff();

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $model = $model::factory()->create();

    Livewire::test(\Lunar\Admin\Support\RelationManagers\MediaRelationManager::class, [
        'ownerRecord' => $model,
        'pageClass' => $page,
    ])->assertSuccessful();
})->with([
    [\Lunar\Models\Product::class, \Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductMedia::class],
    [\Lunar\Models\Brand::class, \Lunar\Admin\Filament\Resources\BrandResource\Pages\ManageBrandMedia::class],
]);
