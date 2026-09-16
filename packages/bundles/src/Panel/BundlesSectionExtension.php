<?php

namespace Lunar\Bundles\Panel;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Bundles\Panel\Http\Controllers\BundleComponentsController;
use Lunar\Bundles\Panel\Http\Controllers\BundleController;
use Lunar\Bundles\Panel\Http\Controllers\BundleGroupsController;
use Lunar\Bundles\Panel\Http\Controllers\ProductBundlesController;
use Lunar\Bundles\Panel\Http\Controllers\VariantSearchController;
use Lunar\Bundles\Panel\Tables\ProductsTableExtension;
use Lunar\Panel\Sections\Catalog\CatalogSection;
use Lunar\Panel\Sections\SectionExtension;
use Lunar\Panel\Slots\Slot;
use Lunar\Panel\Slots\SlotRegistry;

/**
 * Bundles are products, so the extension adds no navigation, permission or
 * search source: the product pages gain the editor through slots and the
 * products table gains a badge and a filter (spec 0085, section 2.8).
 */
class BundlesSectionExtension extends SectionExtension
{
    public function extends(): string
    {
        return 'catalog';
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('bundles')
                ->name('panel.bundles.')
                ->middleware('can:'.CatalogSection::PRODUCTS_PERMISSION)
                ->group(function (): void {
                    Route::get('/search-variants', [VariantSearchController::class, 'search'])->name('search-variants');

                    Route::get('/variants/{variant}', [BundleController::class, 'show'])->name('variants.show');
                    Route::put('/variants/{variant}', [BundleController::class, 'update'])->name('variants.update');
                    Route::delete('/variants/{variant}', [BundleController::class, 'destroy'])->name('variants.destroy');
                    Route::put('/variants/{variant}/components', [BundleComponentsController::class, 'update'])->name('variants.components');
                    Route::put('/variants/{variant}/groups', [BundleGroupsController::class, 'update'])->name('variants.groups');

                    Route::get('/products/{product}', [ProductBundlesController::class, 'show'])->name('products.show');
                    Route::get('/products/{product}/included-in', [ProductBundlesController::class, 'includedIn'])->name('products.included-in');
                });
        };
    }

    public function slots(SlotRegistry $registry): void
    {
        $registry->add(new Slot(
            zone: 'products.edit:variants:after',
            component: 'bundles::BundleCard',
            permission: CatalogSection::PRODUCTS_PERMISSION,
        ));

        $registry->add(new Slot(
            zone: 'products.variants.edit:main:after',
            component: 'bundles::BundleCard',
            permission: CatalogSection::PRODUCTS_PERMISSION,
        ));

        $registry->add(new Slot(
            zone: 'products.edit:sidebar:after',
            component: 'bundles::IncludedInBundlesCard',
            permission: CatalogSection::PRODUCTS_PERMISSION,
        ));
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return ['products.index' => ProductsTableExtension::class];
    }

    /** @return array<int, string> */
    public function langNamespaces(): array
    {
        return ['bundles'];
    }
}
