<?php

use Filament\Actions\Action;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\EditCustomer;
use Lunar\Admin\Support\Extending\EditPageExtension;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Models\Customer;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('extending');

it('can extend header actions', function () {
    $class = new class extends EditPageExtension
    {
        public function headerActions(array $actions): array
        {
            return [
                Action::make('header_action_a'),
            ];
        }
    };

    LunarPanel::extensions([
        EditCustomer::class => $class::class,
    ]);

    $this->asStaff(admin: true);

    $customer = Customer::factory()->create([
        'first_name' => 'Geoff',
    ]);

    Livewire::test(EditCustomer::class, [
        'record' => $customer->getRouteKey(),
    ])->assertActionExists('header_action_a');
});

it('can extend form actions', function () {
    $class = new class extends EditPageExtension
    {
        public function formActions(array $actions): array
        {
            return [
                Action::make('form_action_a'),
            ];
        }
    };

    LunarPanel::extensions([
        EditCustomer::class => $class::class,
    ]);

    $this->asStaff(admin: true);

    $customer = Customer::factory()->create([
        'first_name' => 'Geoff',
    ]);

    Livewire::test(EditCustomer::class, [
        'record' => $customer->getRouteKey(),
    ])->assertActionExists(TestAction::make('form_action_a')->schemaComponent('form-actions', schema: 'content'));
});
