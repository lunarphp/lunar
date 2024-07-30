<?php

use Livewire\Livewire;

uses(\Lunar\Tests\Admin\Unit\Filament\TestCase::class)
    ->group('resource.brand');

it('can return configured relation managers', function () {
    \Lunar\Models\CustomerGroup::factory()->create([
        'default' => true,
    ]);

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $brand = \Lunar\Models\Brand::factory()->create();

    $this->asStaff(admin: true);

    $component = Livewire::test(\Lunar\Admin\Filament\Resources\BrandResource\Pages\ManageBrandMedia::class, [
        'record' => $brand->id,
        'pageClass' => 'brandMediaRelationManager',
    ])->assertSuccessful();

    $managers = $component->instance()->getRelationManagers();

    expect($managers[0])->toBeInstanceOf(\Filament\Resources\RelationManagers\RelationGroup::class);

    expect($managers[0]->getManagers())->toHaveCount(1);
});
