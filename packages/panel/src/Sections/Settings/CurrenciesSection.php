<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\CurrencyCreateController;
use Lunar\Panel\Http\Controllers\Settings\CurrencyEditController;
use Lunar\Panel\Http\Controllers\Settings\CurrencyIndexController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\Settings\Tables\CurrenciesTableExtension;

class CurrenciesSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item. Same handle as the Filament admin's
     * CurrencyResource.
     */
    private const CURRENCIES_PERMISSION = 'settings:core';

    public function key(): string
    {
        return 'currencies';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'currencies.index' => CurrenciesTableExtension::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('store', __('panel::nav.store'), priority: 20);
        $registry->addItem('store', new NavigationItem(
            key: 'currencies',
            label: __('panel::nav.currencies'),
            route: 'panel.settings.currencies.index',
            permission: self::CURRENCIES_PERMISSION,
            priority: 40,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/currencies')
                ->name('panel.settings.currencies.')
                ->middleware('can:'.self::CURRENCIES_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [CurrencyIndexController::class, 'index'])->name('index');
                    Route::post('/', [CurrencyCreateController::class, 'store'])->name('store');
                    Route::get('/{currency}/edit', [CurrencyEditController::class, 'edit'])->name('edit');
                    Route::put('/{currency}', [CurrencyEditController::class, 'update'])->name('update');
                    Route::delete('/{currency}', [CurrencyEditController::class, 'destroy'])->name('destroy');
                });
        };
    }
}
