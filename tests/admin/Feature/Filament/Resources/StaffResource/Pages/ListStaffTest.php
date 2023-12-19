<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\StaffResource;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.staff');

it('can render staff index page', function () {
    $this->asStaff(admin: true)
        ->get(StaffResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list staff', function () {
    $this->asStaff();

    $staffs = \Lunar\Admin\Models\Staff::factory(5)->create();

    Livewire::test(\Lunar\Admin\Filament\Resources\StaffResource\Pages\ListStaff::class)
        ->assertCountTableRecords(6)
        ->assertCanSeeTableRecords($staffs);
});
