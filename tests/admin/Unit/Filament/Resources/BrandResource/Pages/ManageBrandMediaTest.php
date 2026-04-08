<?php

use Filament\Resources\RelationManagers\RelationGroup;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\ManageBrandMedia;
use Lunar\Models\Brand;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Tests\Admin\Unit\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.brand');

it('can return configured relation managers', function () {
    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    Language::factory()->create([
        'default' => true,
    ]);

    $brand = Brand::factory()->create();

    $this->asStaff(admin: true);

    $component = Livewire::test(ManageBrandMedia::class, [
        'record' => $brand->id,
        'pageClass' => 'brandMediaRelationManager',
    ])->assertSuccessful();

    $managers = $component->instance()->getRelationManagers();

    expect($managers[0])->toBeInstanceOf(RelationGroup::class);

    expect($managers[0]->getManagers())->toHaveCount(1);
});
