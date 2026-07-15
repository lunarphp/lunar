<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\ChannelCreateController;
use Lunar\Panel\Http\Controllers\Settings\ChannelEditController;
use Lunar\Panel\Http\Controllers\Settings\ChannelIndexController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\Settings\Tables\ChannelsTableExtension;

class ChannelsSection extends Section
{
    public function key(): string
    {
        return 'channels';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'channels.index' => ChannelsTableExtension::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('general', __('panel::nav.general'));
        $registry->addItem('general', new NavigationItem(
            key: 'channels',
            label: __('panel::nav.channels'),
            route: 'panel.settings.channels.index',
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::get('settings/channels', [ChannelIndexController::class, 'index'])
                ->name('panel.settings.channels.index');

            Route::post('settings/channels', [ChannelCreateController::class, 'store'])
                ->name('panel.settings.channels.store');

            Route::get('settings/channels/{channel}/edit', [ChannelEditController::class, 'edit'])
                ->name('panel.settings.channels.edit');

            Route::put('settings/channels/{channel}', [ChannelEditController::class, 'update'])
                ->name('panel.settings.channels.update');

            Route::delete('settings/channels/{channel}', [ChannelEditController::class, 'destroy'])
                ->name('panel.settings.channels.destroy');
        };
    }
}
