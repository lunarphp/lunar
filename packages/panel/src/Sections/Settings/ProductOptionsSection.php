<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\ProductOptionCreateController;
use Lunar\Panel\Http\Controllers\Settings\ProductOptionEditController;
use Lunar\Panel\Http\Controllers\Settings\ProductOptionIndexController;
use Lunar\Panel\Http\Controllers\Settings\ProductOptionValueSwatchController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\Settings\Tables\ProductOptionsTableExtension;

class ProductOptionsSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item.
     */
    private const PRODUCT_OPTIONS_PERMISSION = 'settings:core';

    public function key(): string
    {
        return 'product-options';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'product-options.index' => ProductOptionsTableExtension::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('catalog_settings', __('panel::nav.catalog'), priority: 40);
        $registry->addItem('catalog_settings', new NavigationItem(
            key: 'product-options',
            label: __('panel::nav.product_options'),
            route: 'panel.settings.product-options.index',
            permission: self::PRODUCT_OPTIONS_PERMISSION,
            priority: 30,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/product-options')
                ->name('panel.settings.product-options.')
                ->middleware('can:'.self::PRODUCT_OPTIONS_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [ProductOptionIndexController::class, 'index'])->name('index');
                    Route::post('/', [ProductOptionCreateController::class, 'store'])->name('store');
                    Route::get('/{productOption}/edit', [ProductOptionEditController::class, 'edit'])->name('edit');
                    Route::put('/{productOption}', [ProductOptionEditController::class, 'update'])->name('update');
                    Route::delete('/{productOption}', [ProductOptionEditController::class, 'destroy'])->name('destroy');

                    Route::post('/{productOption}/values/{value}/swatch', [ProductOptionValueSwatchController::class, 'store'])
                        ->scopeBindings()->name('values.swatch.store');
                    Route::delete('/{productOption}/values/{value}/swatch', [ProductOptionValueSwatchController::class, 'destroy'])
                        ->scopeBindings()->name('values.swatch.destroy');
                });
        };
    }
}
