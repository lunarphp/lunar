<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\RegionCreateController;
use Lunar\Panel\Http\Controllers\Settings\RegionEditController;
use Lunar\Panel\Http\Controllers\Settings\RegionIndexController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\Settings\Tables\RegionsTableExtension;

class RegionsSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item. Same handle as the Filament
     * admin's RegionResource.
     */
    private const REGIONS_PERMISSION = 'settings:core';

    public function key(): string
    {
        return 'regions';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'regions.index' => RegionsTableExtension::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('store', __('panel::nav.store'), priority: 20);
        $registry->addItem('store', new NavigationItem(
            key: 'regions',
            label: __('panel::nav.regions'),
            route: 'panel.settings.regions.index',
            permission: self::REGIONS_PERMISSION,
            priority: 20,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/regions')
                ->name('panel.settings.regions.')
                ->middleware('can:'.self::REGIONS_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [RegionIndexController::class, 'index'])->name('index');
                    Route::post('/', [RegionCreateController::class, 'store'])->name('store');
                    Route::get('/{region}/edit', [RegionEditController::class, 'edit'])->name('edit');
                    Route::put('/{region}', [RegionEditController::class, 'update'])->name('update');
                    Route::delete('/{region}', [RegionEditController::class, 'destroy'])->name('destroy');
                });
        };
    }
}
