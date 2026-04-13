<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\EditCustomer;
use Lunar\Admin\Support\Extending\EditPageExtension;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Models\Customer;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('extending.edit');

it('can change data before fill', function () {
    $class = new class extends EditPageExtension
    {
        public function beforeFill(array $data): array
        {
            $data['first_name'] = 'Jacob';

            return $data;
        }
    };

    $customer = Customer::factory()->create([
        'first_name' => 'Geoff',
    ]);

    LunarPanel::extensions([
        EditCustomer::class => $class::class,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(EditCustomer::class, [
        'record' => $customer->getRouteKey(),
    ])->assertFormSet([
        'first_name' => 'Jacob',
    ])->call('save');

    $this->assertDatabaseHas(Customer::class, [
        'first_name' => 'Jacob',
    ]);
});

it('can change data before save', function () {
    $class = new class extends EditPageExtension
    {
        public function beforeSave(array $data): array
        {
            $data['first_name'] = 'Tony';

            return $data;
        }
    };

    $customer = Customer::factory()->create([
        'first_name' => 'Geoff',
    ]);

    LunarPanel::extensions([
        EditCustomer::class => $class::class,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(EditCustomer::class, [
        'record' => $customer->getRouteKey(),
    ])->assertFormSet([
        'first_name' => 'Geoff',
    ])->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Customer::class, [
        'first_name' => 'Tony',
    ]);
});
