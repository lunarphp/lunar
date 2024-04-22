<?php

use Lunar\Admin\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use Lunar\Admin\Support\Facades\LunarPanel;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('extending');

it('can customise page headings', function () {
    $class = new class extends \Lunar\Admin\Support\Extending\CreatePageExtension
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

    \Livewire\Livewire::test(CreateCustomer::class)
        ->assertSee('New Heading')
        ->assertSee('New Subheading');
});

it('can change data before creation', function () {
    $class = new class extends \Lunar\Admin\Support\Extending\CreatePageExtension
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

    \Livewire\Livewire::test(CreateCustomer::class)
        ->fillForm([
            'title' => 'Mr',
            'first_name' => 'Jeff',
            'last_name' => 'Bloggs',
        ])->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(\Lunar\Models\Customer::class, [
        'first_name' => 'Jacob',
    ]);
});

it('can manipulate model after creation', function () {
    $class = new class extends \Lunar\Admin\Support\Extending\CreatePageExtension
    {
        public function afterCreation(Illuminate\Database\Eloquent\Model $record, array $data): Illuminate\Database\Eloquent\Model
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

    \Livewire\Livewire::test(CreateCustomer::class)
        ->fillForm([
            'title' => 'Mr',
            'first_name' => 'Jeff',
            'last_name' => 'Bloggs',
        ])->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(\Lunar\Models\Customer::class, [
        'first_name' => 'Geoff',
    ]);
});
