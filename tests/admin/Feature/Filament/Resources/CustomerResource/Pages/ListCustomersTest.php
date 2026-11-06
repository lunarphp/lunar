<?php

use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CustomerResource;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\ListCustomers;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.customer');

it('can render customer index page', function () {
    $this->asStaff(admin: true)
        ->get(CustomerResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list customers', function () {
    $this->asStaff();

    $customers = Customer::factory(5)->create();

    Livewire::test(ListCustomers::class)
        ->assertCountTableRecords(5)
        ->assertCanSeeTableRecords($customers);
});

it('can delete a customer from the table', function () {
    $customer = Customer::factory()->create();

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(ListCustomers::class)
        ->callTableAction('delete', $customer);

    $this->assertModelMissing($customer);
});

it('can bulk delete customers', function () {
    $customers = Customer::factory(3)->create();

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(ListCustomers::class)
        ->callTableBulkAction('delete', $customers);

    foreach ($customers as $customer) {
        $this->assertModelMissing($customer);
    }
});

it('can filter customers by customer group', function () {
    $this->asStaff();

    $group = CustomerGroup::factory()->create();
    $inGroup = Customer::factory()->create();
    $outOfGroup = Customer::factory()->create();

    $inGroup->customerGroups()->attach($group);

    Livewire::test(ListCustomers::class)
        ->filterTable('customer_group', [$group->id])
        ->assertCanSeeTableRecords([$inGroup])
        ->assertCanNotSeeTableRecords([$outOfGroup]);
});

it('does not use DISTINCT ON when filtering by customer group on PostgreSQL', function () {
    $this->asStaff();

    $group = CustomerGroup::factory()->create();

    $connection = DB::connection();
    $connection->setQueryGrammar(new PostgresGrammar($connection));

    $queries = $connection->pretend(function () use ($group) {
        Livewire::test(ListCustomers::class)
            ->filterTable('customer_group', [$group->id]);
    });

    $sql = collect($queries)->pluck('query')->implode("\n");

    expect($sql)->toContain('lunar_customer_groups')
        ->and(strtolower($sql))->not->toContain('distinct on');
});
