<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\StaffResource;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\ListStaff;
use Lunar\Admin\Models\Staff;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.staff');

it('can render staff index page', function () {
    $this->asStaff(admin: true)
        ->get(StaffResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list staff', function () {
    $this->asStaff();

    $staffs = Staff::factory(5)->create();

    Livewire::test(ListStaff::class)
        ->assertCountTableRecords(6)
        ->assertCanSeeTableRecords($staffs);
});
