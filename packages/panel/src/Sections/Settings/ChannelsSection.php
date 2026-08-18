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
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item. Same handle as the Filament admin's
     * ChannelResource.
     */
    private const CHANNELS_PERMISSION = 'settings:core';

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
        $registry->group('store', __('panel::nav.store'), priority: 20);
        $registry->addItem('store', new NavigationItem(
            key: 'channels',
            label: __('panel::nav.channels'),
            route: 'panel.settings.channels.index',
            permission: self::CHANNELS_PERMISSION,
            priority: 10,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/channels')
                ->name('panel.settings.channels.')
                ->middleware('can:'.self::CHANNELS_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [ChannelIndexController::class, 'index'])->name('index');
                    Route::post('/', [ChannelCreateController::class, 'store'])->name('store');
                    Route::get('/{channel}/edit', [ChannelEditController::class, 'edit'])->name('edit');
                    Route::put('/{channel}', [ChannelEditController::class, 'update'])->name('update');
                    Route::delete('/{channel}', [ChannelEditController::class, 'destroy'])->name('destroy');
                });
        };
    }
}
