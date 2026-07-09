<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\ListBrands;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.brand.search');

it('can search brand by name on brand list', function () {

    Config::set('lunar.admin.scout_enabled', false);

    $this->asStaff(admin: true);

    Language::factory()->create([
        'default' => true,
    ]);

    $brands = Brand::factory()->count(10)->create();

    $name = $brands->first()->name;

    Livewire::test(ListBrands::class)
        ->searchTable($name)
        ->assertCanSeeTableRecords($brands->where('name', $name));
});
