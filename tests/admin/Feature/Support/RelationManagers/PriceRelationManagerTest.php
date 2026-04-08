<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductPricing;
use Lunar\Admin\Support\RelationManagers\PriceRelationManager;
use Lunar\Models\Language;
use Lunar\Models\Product;
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
