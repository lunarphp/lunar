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

    Livewire::test(\Lunar\Admin\Support\RelationManagers\PriceRelationManager::class, [
        'ownerRecord' => $model,
        'pageClass' => $page,
    ])->assertSuccessful();
})->with([
    'product' => [
        'model' => \Lunar\Models\Product::class,
        'page' => \Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductPricing::class,
    ],
]);
