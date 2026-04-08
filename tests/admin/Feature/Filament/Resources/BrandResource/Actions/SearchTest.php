<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\BrandResource\Pages\ListBrands;
use Lunar\Models\Brand;
use Lunar\Models\Language;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.brand.search');

it('can search brand by name on brand list', function () {

    Config::set('lunar.panel.scout_enabled', false);

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
