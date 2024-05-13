<?php

use Lunar\Admin\Filament\Resources\ChannelResource;
use \Lunar\Admin\Filament\Resources\ChannelResource\Pages\CreateChannel;
use Lunar\Admin\Support\Facades\LunarPanel;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('extending');

it('can extend header actions', function () {
    $class = new class extends \Lunar\Admin\Support\Extending\CreatePageExtension
    {
        function headerActions(array $actions): array
        {
            return [
                \Filament\Actions\Action::make('header_action_a'),
            ];
        }
    };

    LunarPanel::extensions([
        ChannelResource\Pages\CreateChannel::class => $class::class,
    ]);

    $this->asStaff(admin: true);

    \Livewire\Livewire::test(ChannelResource\Pages\CreateChannel::class)
        ->assertActionExists('header_action_a');
});

it('can extend form actions', function () {
    $class = new class extends \Lunar\Admin\Support\Extending\CreatePageExtension
    {
        function formActions(array $actions): array
        {
            return [
                \Filament\Actions\Action::make('form_action_a'),
            ];
        }
    };

    LunarPanel::extensions([
        ChannelResource\Pages\CreateChannel::class => $class::class,
    ]);

    $this->asStaff(admin: true);

    \Livewire\Livewire::test(ChannelResource\Pages\CreateChannel::class)
        ->assertActionExists('form_action_a');
});
