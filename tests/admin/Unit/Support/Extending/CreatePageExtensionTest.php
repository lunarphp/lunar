<?php

use Filament\Actions\Action;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ChannelResource;
use Lunar\Admin\Support\Extending\CreatePageExtension;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('extending');

it('can extend header actions', function () {
    $class = new class extends CreatePageExtension
    {
        public function headerActions(array $actions): array
        {
            return [
                Action::make('header_action_a'),
            ];
        }
    };

    LunarPanel::extensions([
        ChannelResource\Pages\CreateChannel::class => $class::class,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(ChannelResource\Pages\CreateChannel::class)
        ->assertActionExists('header_action_a');
});

it('can extend form actions', function () {
    $class = new class extends CreatePageExtension
    {
        public function formActions(array $actions): array
        {
            return [
                Action::make('form_action_a'),
            ];
        }
    };

    LunarPanel::extensions([
        ChannelResource\Pages\CreateChannel::class => $class::class,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(ChannelResource\Pages\CreateChannel::class)
        ->assertActionExists(TestAction::make('form_action_a')->schemaComponent('form-actions', schema: 'content'));
});
