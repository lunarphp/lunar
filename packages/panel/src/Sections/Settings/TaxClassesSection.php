<?php

namespace Lunar\Panel\Sections\Settings;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Settings\TaxClassCreateController;
use Lunar\Panel\Http\Controllers\Settings\TaxClassEditController;
use Lunar\Panel\Http\Controllers\Settings\TaxClassIndexController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Sections\Settings\Tables\TaxClassesTableExtension;

class TaxClassesSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the settings navigation item. Same handle as the Filament admin's
     * TaxClassResource.
     */
    private const TAX_CLASSES_PERMISSION = 'settings:core';

    public function key(): string
    {
        return 'tax-classes';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'tax-classes.index' => TaxClassesTableExtension::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('taxation', __('panel::nav.taxation'), priority: 30);
        $registry->addItem('taxation', new NavigationItem(
            key: 'tax-classes',
            label: __('panel::nav.tax_classes'),
            route: 'panel.settings.tax-classes.index',
            permission: self::TAX_CLASSES_PERMISSION,
            priority: 10,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/tax-classes')
                ->name('panel.settings.tax-classes.')
                ->middleware('can:'.self::TAX_CLASSES_PERMISSION)
                ->group(function (): void {
                    Route::get('/', [TaxClassIndexController::class, 'index'])->name('index');
                    Route::post('/', [TaxClassCreateController::class, 'store'])->name('store');
                    Route::get('/{taxClass}/edit', [TaxClassEditController::class, 'edit'])->name('edit');
                    Route::put('/{taxClass}', [TaxClassEditController::class, 'update'])->name('update');
                    Route::delete('/{taxClass}', [TaxClassEditController::class, 'destroy'])->name('destroy');
                });
        };
    }
}
