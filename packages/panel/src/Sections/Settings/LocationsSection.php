<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\LocationCreateController;
use Lunar\Panel\Http\Controllers\Settings\LocationEditController;
use Lunar\Panel\Http\Controllers\Settings\LocationIndexController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\Settings\Tables\LocationsTableExtension;

class LocationsSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item. Same handle as the Filament
     * admin's LocationResource.
     */
    private const LOCATIONS_PERMISSION = 'settings:core';

    public function key(): string
    {
        return 'locations';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'locations.index' => LocationsTableExtension::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('store', __('panel::nav.store'), priority: 20);
        $registry->addItem('store', new NavigationItem(
            key: 'locations',
            label: __('panel::nav.locations'),
            route: 'panel.settings.locations.index',
            permission: self::LOCATIONS_PERMISSION,
            priority: 90,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/locations')
                ->name('panel.settings.locations.')
                ->middleware('can:'.self::LOCATIONS_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [LocationIndexController::class, 'index'])->name('index');
                    Route::post('/', [LocationCreateController::class, 'store'])->name('store');
                    Route::get('/{location}/edit', [LocationEditController::class, 'edit'])->name('edit');
                    Route::put('/{location}', [LocationEditController::class, 'update'])->name('update');
                    Route::delete('/{location}', [LocationEditController::class, 'destroy'])->name('destroy');
                });
        };
    }
}
