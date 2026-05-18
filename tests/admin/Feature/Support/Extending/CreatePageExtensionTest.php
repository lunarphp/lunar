<?php

use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use Lunar\Admin\Support\Extending\CreatePageExtension;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Models\Customer;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('extending');

it('can customise page headings', function () {
    $class = new class extends CreatePageExtension
    {
        public function heading($title): string
        {
            return 'New Heading';
        }

        public function subheading($title): string
        {
            return 'New Subheading';
        }
    };

    LunarPanel::extensions([
        CreateCustomer::class => $class::class,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(CreateCustomer::class)
        ->assertSee('New Heading')
        ->assertSee('New Subheading');
});

it('can change data before creation', function () {
    $class = new class extends CreatePageExtension
    {
        public function beforeCreate(array $data): array
        {
            $data['first_name'] = 'Jacob';

            return $data;
        }
    };

    LunarPanel::extensions([
        CreateCustomer::class => $class::class,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(CreateCustomer::class)
        ->fillForm([
            'title' => 'Mr',
            'first_name' => 'Jeff',
            'last_name' => 'Bloggs',
        ])->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Customer::class, [
        'first_name' => 'Jacob',
    ]);
});

it('can manipulate model after creation', function () {
    $class = new class extends CreatePageExtension
    {
        public function afterCreation(Model $record, array $data): Model
        {
            $record->update([
                'first_name' => 'Geoff',
            ]);

            return $record;
        }
    };

    LunarPanel::extensions([
        CreateCustomer::class => $class::class,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(CreateCustomer::class)
        ->fillForm([
            'title' => 'Mr',
            'first_name' => 'Jeff',
            'last_name' => 'Bloggs',
        ])->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Customer::class, [
        'first_name' => 'Geoff',
    ]);
});
