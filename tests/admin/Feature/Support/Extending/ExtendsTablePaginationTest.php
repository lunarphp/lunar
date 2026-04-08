<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\ListCustomers;
use Lunar\Models\Customer;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('extending.list');

it('can list all records', function () {
    $this->asStaff();

    $customers = Customer::factory(30)->create();

    Livewire::test(ListCustomers::class)
        ->set('tableRecordsPerPage', 'all')
        ->assertCountTableRecords(30)
        ->assertCanSeeTableRecords($customers);
});
