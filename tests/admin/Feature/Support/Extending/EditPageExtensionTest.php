<?php

use Lunar\Admin\Filament\Resources\CustomerResource\Pages\EditCustomer;
use Lunar\Admin\Support\Facades\LunarPanel;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('extending.edit');

it('can change data before fill', function () {
    $class = new class extends \Lunar\Admin\Support\Extending\EditPageExtension
    {
        public function beforeFill(array $data): array
        {
            $data['first_name'] = 'Jacob';

            return $data;
        }
    };

    $customer = \Lunar\Models\Customer::factory()->create([
        'first_name' => 'Geoff',
    ]);

    LunarPanel::extensions([
        $class::class => EditCustomer::class,
    ]);

    $this->asStaff(admin: true);

    \Livewire\Livewire::test(EditCustomer::class, [
        'record' => $customer->getRouteKey(),
    ])->assertFormSet([
        'first_name' => 'Jacob',
    ])->call('save');

    $this->assertDatabaseHas(\Lunar\Models\Customer::class, [
        'first_name' => 'Jacob',
    ]);
});

it('can change data before save', function () {
    $class = new class extends \Lunar\Admin\Support\Extending\EditPageExtension
    {
        public function beforeSave(array $data): array
        {
            $data['first_name'] = 'Tony';

            return $data;
        }
    };

    $customer = \Lunar\Models\Customer::factory()->create([
        'first_name' => 'Geoff',
    ]);

    LunarPanel::extensions([
        $class::class => EditCustomer::class,
    ]);

    $this->asStaff(admin: true);

    \Livewire\Livewire::test(EditCustomer::class, [
        'record' => $customer->getRouteKey(),
    ])->assertFormSet([
        'first_name' => 'Geoff',
    ])->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(\Lunar\Models\Customer::class, [
        'first_name' => 'Tony',
    ]);
});
