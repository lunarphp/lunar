<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\CountryEditController;
use Lunar\Panel\Http\Controllers\Settings\CountryIndexController;
use Lunar\Panel\Http\Controllers\Settings\CountryStateController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\Settings\Tables\CountriesTableExtension;

class CountriesSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item.
     */
    private const COUNTRIES_PERMISSION = 'settings:core';

    public function key(): string
    {
        return 'countries';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'countries.index' => CountriesTableExtension::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('store', __('panel::nav.store'), priority: 20);
        $registry->addItem('store', new NavigationItem(
            key: 'countries',
            label: __('panel::nav.countries'),
            route: 'panel.settings.countries.index',
            permission: self::COUNTRIES_PERMISSION,
            priority: 60,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/countries')
                ->name('panel.settings.countries.')
                ->middleware('can:'.self::COUNTRIES_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [CountryIndexController::class, 'index'])->name('index');
                    Route::get('/{country}/edit', [CountryEditController::class, 'edit'])->name('edit');
                    Route::put('/{country}', [CountryEditController::class, 'update'])->name('update');
                    Route::delete('/{country}', [CountryEditController::class, 'destroy'])->name('destroy');
                    Route::post('/{country}/states', [CountryStateController::class, 'store'])->name('states.store');
                    Route::delete('/{country}/states/{state}', [CountryStateController::class, 'destroy'])
                        ->scopeBindings()
                        ->name('states.destroy');
                });
        };
    }
}
