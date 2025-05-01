<?php

use Livewire\Livewire;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('extending.list');

it('can list all records', function () {
    $this->asStaff();

    $customers = \Lunar\Models\Customer::factory(30)->create();

    Livewire::test(\Lunar\Admin\Filament\Resources\CustomerResource\Pages\ListCustomers::class)
        ->set('tableRecordsPerPage', 'all')
        ->assertCountTableRecords(30)
        ->assertCanSeeTableRecords($customers);
});
