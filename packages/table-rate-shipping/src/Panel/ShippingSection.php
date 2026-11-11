<?php

namespace Lunar\Shipping\Panel;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;
use Lunar\Shipping\DiscountTypes\ShippingDiscount;
use Lunar\Shipping\Panel\DiscountTypeForms\ShippingDiscountForm;
use Lunar\Shipping\Panel\Http\Controllers\ExclusionListCreateController;
use Lunar\Shipping\Panel\Http\Controllers\ExclusionListEditController;
use Lunar\Shipping\Panel\Http\Controllers\ExclusionListIndexController;
use Lunar\Shipping\Panel\Http\Controllers\ExclusionListProductSearchController;
use Lunar\Shipping\Panel\Http\Controllers\MethodCreateController;
use Lunar\Shipping\Panel\Http\Controllers\MethodEditController;
use Lunar\Shipping\Panel\Http\Controllers\MethodIndexController;
use Lunar\Shipping\Panel\Http\Controllers\ZoneCreateController;
use Lunar\Shipping\Panel\Http\Controllers\ZoneEditController;
use Lunar\Shipping\Panel\Http\Controllers\ZoneIndexController;
use Lunar\Shipping\Panel\Http\Controllers\ZoneRateController;
use Lunar\Shipping\Panel\Tables\ExclusionListsTableExtension;
use Lunar\Shipping\Panel\Tables\MethodsTableExtension;
use Lunar\Shipping\Panel\Tables\ZonesTableExtension;

/**
 * Settings > Shipping in the Inertia panel: zones, methods and exclusion
 * lists, gated on the same permission handle as the Filament resources.
 */
class ShippingSection extends Section
{
    public const PERMISSION = 'shipping:manage';

    public function key(): string
    {
        return 'shipping';
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'shipping.zones.index' => ZonesTableExtension::class,
            'shipping.methods.index' => MethodsTableExtension::class,
            'shipping.exclusion-lists.index' => ExclusionListsTableExtension::class,
        ];
    }

    /** @return array<class-string, class-string> */
    public function discountTypeForms(): array
    {
        return [
            ShippingDiscount::class => ShippingDiscountForm::class,
        ];
    }

    public function settingsNavigation(NavigationRegistry $registry): void
    {
        $registry->group('shipping', 'shipping::nav.shipping', priority: 25);

        $registry->addItem('shipping', new NavigationItem(
            key: 'shipping-zones',
            label: 'shipping::nav.zones',
            route: 'panel.settings.shipping.zones.index',
            permission: self::PERMISSION,
            priority: 10,
        ));

        $registry->addItem('shipping', new NavigationItem(
            key: 'shipping-methods',
            label: 'shipping::nav.methods',
            route: 'panel.settings.shipping.methods.index',
            permission: self::PERMISSION,
            priority: 20,
        ));

        $registry->addItem('shipping', new NavigationItem(
            key: 'shipping-exclusion-lists',
            label: 'shipping::nav.exclusion_lists',
            route: 'panel.settings.shipping.exclusion-lists.index',
            permission: self::PERMISSION,
            priority: 30,
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('settings/shipping')
                ->name('panel.settings.shipping.')
                ->middleware('can:'.self::PERMISSION)
                ->group(function (): void {
                    Route::prefix('zones')->name('zones.')->group(function (): void {
                        Route::get('/', [ZoneIndexController::class, 'index'])->name('index');
                        Route::post('/', [ZoneCreateController::class, 'store'])->name('store');
                        Route::get('/{shippingZone}/edit', [ZoneEditController::class, 'edit'])->name('edit');
                        Route::put('/{shippingZone}', [ZoneEditController::class, 'update'])->name('update');
                        Route::delete('/{shippingZone}', [ZoneEditController::class, 'destroy'])->name('destroy');
                        Route::post('/{shippingZone}/rates', [ZoneRateController::class, 'store'])->name('rates.store');
                        Route::put('/{shippingZone}/rates/{shippingRate}', [ZoneRateController::class, 'update'])->name('rates.update');
                        Route::delete('/{shippingZone}/rates/{shippingRate}', [ZoneRateController::class, 'destroy'])->name('rates.destroy');
                    });

                    Route::prefix('methods')->name('methods.')->group(function (): void {
                        Route::get('/', [MethodIndexController::class, 'index'])->name('index');
                        Route::post('/', [MethodCreateController::class, 'store'])->name('store');
                        Route::get('/{shippingMethod}/edit', [MethodEditController::class, 'edit'])->name('edit');
                        Route::put('/{shippingMethod}', [MethodEditController::class, 'update'])->name('update');
                        Route::delete('/{shippingMethod}', [MethodEditController::class, 'destroy'])->name('destroy');
                    });

                    Route::prefix('exclusion-lists')->name('exclusion-lists.')->group(function (): void {
                        Route::get('/', [ExclusionListIndexController::class, 'index'])->name('index');
                        Route::post('/', [ExclusionListCreateController::class, 'store'])->name('store');
                        Route::get('/{shippingExclusionList}/edit', [ExclusionListEditController::class, 'edit'])->name('edit');
                        Route::put('/{shippingExclusionList}', [ExclusionListEditController::class, 'update'])->name('update');
                        Route::delete('/{shippingExclusionList}', [ExclusionListEditController::class, 'destroy'])->name('destroy');
                        Route::get('/{shippingExclusionList}/products/search', [ExclusionListProductSearchController::class, 'search'])->name('products.search');
                    });
                });
        };
    }

    /**
     * @return array{input: string, hotFile: null, buildDirectory: string, __buildSourcePath: string}
     */
    public function vite(): array
    {
        return [
            'input' => 'resources/js/panel.ts',
            'hotFile' => null,
            'buildDirectory' => 'vendor/lunar-panel/shipping',
            '__buildSourcePath' => dirname(__DIR__, 2).'/build',
        ];
    }

    /** @return array<int, string> */
    public function langNamespaces(): array
    {
        return ['shipping'];
    }
}
