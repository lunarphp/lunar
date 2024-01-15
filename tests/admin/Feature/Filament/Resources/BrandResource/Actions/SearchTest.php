<?php

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.brand.search');

it('can search brand by name on brand list', function () {

    Config::set('lunar.search.scout_enabled', false);

    $this->asStaff(admin: true);

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $brands = \Lunar\Models\Brand::factory()->count(10)->create();

    $name = $brands->first()->name;

    \Livewire\Livewire::test(Lunar\Admin\Filament\Resources\BrandResource\Pages\ListBrands::class)
        ->searchTable($name)
        ->assertCanSeeTableRecords($brands->where('name', $name));
});
