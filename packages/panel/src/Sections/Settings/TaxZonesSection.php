<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\TaxZoneCreateController;
use Lunar\Panel\Http\Controllers\Settings\TaxZoneEditController;
use Lunar\Panel\Http\Controllers\Settings\TaxZoneIndexController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\Settings\Tables\TaxZonesTableExtension;

class TaxZonesSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item. Same handle as the Filament admin's
     * TaxZoneResource.
     */
    private const TAX_ZONES_PERMISSION = 'settings:core';

    public function key(): string
    {
        return 'tax-zones';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'tax-zones.index' => TaxZonesTableExtension::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('taxation', __('panel::nav.taxation'), priority: 30);
        $registry->addItem('taxation', new NavigationItem(
            key: 'tax-zones',
            label: __('panel::nav.tax_zones'),
            route: 'panel.settings.tax-zones.index',
            permission: self::TAX_ZONES_PERMISSION,
            priority: 20,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/tax-zones')
                ->name('panel.settings.tax-zones.')
                ->middleware('can:'.self::TAX_ZONES_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [TaxZoneIndexController::class, 'index'])->name('index');
                    Route::post('/', [TaxZoneCreateController::class, 'store'])->name('store');
                    Route::get('/{taxZone}/edit', [TaxZoneEditController::class, 'edit'])->name('edit');
                    Route::put('/{taxZone}', [TaxZoneEditController::class, 'update'])->name('update');
                    Route::delete('/{taxZone}', [TaxZoneEditController::class, 'destroy'])->name('destroy');
                });
        };
    }
}
