<?php

namespace Lunar\Panel\Sections\Catalog;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Contracts\DraftableResource;
use Lunar\Panel\Http\Controllers\Brands\BrandBulkStatusController;
use Lunar\Panel\Http\Controllers\Brands\BrandCreateController;
use Lunar\Panel\Http\Controllers\Brands\BrandEditController;
use Lunar\Panel\Http\Controllers\Brands\BrandIndexController;
use Lunar\Panel\Http\Controllers\Brands\BrandMediaController;
use Lunar\Panel\Http\Controllers\Brands\BrandUrlController;
use Lunar\Panel\Http\Controllers\Catalog\CollectionSearchController;
use Lunar\Panel\Http\Controllers\Catalog\ProductSearchController;
use Lunar\Panel\Http\Controllers\Collections\CollectionCreateController;
use Lunar\Panel\Http\Controllers\Collections\CollectionEditController;
use Lunar\Panel\Http\Controllers\Collections\CollectionGroupController;
use Lunar\Panel\Http\Controllers\Collections\CollectionIndexController;
use Lunar\Panel\Http\Controllers\Collections\CollectionMediaController;
use Lunar\Panel\Http\Controllers\Collections\CollectionMoveController;
use Lunar\Panel\Http\Controllers\Collections\CollectionProductsController;
use Lunar\Panel\Http\Controllers\Collections\CollectionUrlController;
use Lunar\Panel\Http\Controllers\EditDraftController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Catalog\Tables\BrandsTableExtension;
use Lunar\Panel\Sections\Catalog\Tables\CollectionsTableExtension;
use Lunar\Panel\Sections\Section;

class CatalogSection extends Section
{
    /**
     * Manifest permission handles gating both the routes (via can: middleware)
     * and the navigation items, so what a user can see and what they can reach
     * stay in lockstep. Same handles as the Filament admin's resources.
     */
    public const BRANDS_PERMISSION = 'catalog:manage-products';

    public const COLLECTIONS_PERMISSION = 'catalog:manage-collections';

    public function key(): string
    {
        return 'catalog';
    }

    public function label(): string
    {
        return __('panel::nav.catalog');
    }

    public function navigation(NavigationRegistry $registry): void
    {
        $registry->group('catalog', __('panel::nav.catalog'));
        $registry->addItem('catalog', new NavigationItem(
            key: 'brands',
            label: __('panel::nav.brands'),
            icon: 'tag',
            route: 'panel.brands.index',
            permission: self::BRANDS_PERMISSION,
        ));
        $registry->addItem('catalog', new NavigationItem(
            key: 'collections',
            label: __('panel::nav.collections'),
            icon: 'folder',
            route: 'panel.collections.index',
            permission: self::COLLECTIONS_PERMISSION,
        ));
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'brands.index' => BrandsTableExtension::class,
            'collections.index' => CollectionsTableExtension::class,
        ];
    }

    /** @return array<int, class-string<DraftableResource>> */
    public function draftables(): array
    {
        return [
            BrandDraftResource::class,
            CollectionDraftResource::class,
        ];
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('catalog')->name('panel.catalog.')->middleware('can:'.self::BRANDS_PERMISSION)->group(function (): void {
                Route::get('/collections/search', [CollectionSearchController::class, 'search'])->name('collections.search');
                Route::get('/products/search', [ProductSearchController::class, 'search'])->name('products.search');
            });

            Route::prefix('brands')->name('panel.brands.')->middleware('can:'.self::BRANDS_PERMISSION)->group(function (): void {
                Route::get('/', [BrandIndexController::class, 'index'])->name('index');
                Route::get('/create', [BrandCreateController::class, 'create'])->name('create');
                Route::post('/', [BrandCreateController::class, 'store'])->name('store');
                Route::post('/bulk/status/{status}', [BrandBulkStatusController::class, 'update'])
                    ->whereIn('status', ['active', 'draft'])
                    ->name('bulk-status');
                Route::get('/{brand}/edit', [BrandEditController::class, 'edit'])->name('edit');
                Route::put('/{brand}', [BrandEditController::class, 'update'])->name('update');
                Route::delete('/{brand}', [BrandEditController::class, 'destroy'])->name('destroy');

                Route::patch('/{brand}/draft', [EditDraftController::class, 'update'])->name('draft.update');
                Route::delete('/{brand}/draft', [EditDraftController::class, 'destroy'])->name('draft.destroy');
                Route::post('/{brand}/draft/commit', [EditDraftController::class, 'commit'])->name('draft.commit');

                Route::scopeBindings()->group(function (): void {
                    Route::post('/{brand}/urls', [BrandUrlController::class, 'store'])->name('urls.store');
                    Route::put('/{brand}/urls/{url}', [BrandUrlController::class, 'update'])->name('urls.update');
                    Route::delete('/{brand}/urls/{url}', [BrandUrlController::class, 'destroy'])->name('urls.destroy');

                    Route::post('/{brand}/media', [BrandMediaController::class, 'store'])->name('media.store');
                    Route::post('/{brand}/media/reorder', [BrandMediaController::class, 'reorder'])->name('media.reorder');
                    Route::put('/{brand}/media/{media}', [BrandMediaController::class, 'update'])->name('media.update');
                    Route::delete('/{brand}/media/{media}', [BrandMediaController::class, 'destroy'])->name('media.destroy');
                });
            });

            Route::prefix('collections')->name('panel.collections.')->middleware('can:'.self::COLLECTIONS_PERMISSION)->group(function (): void {
                Route::get('/', [CollectionIndexController::class, 'index'])->name('index');
                Route::get('/create', [CollectionCreateController::class, 'create'])->name('create');
                Route::post('/', [CollectionCreateController::class, 'store'])->name('store');

                Route::post('/groups', [CollectionGroupController::class, 'store'])->name('groups.store');
                Route::put('/groups/{collectionGroup}', [CollectionGroupController::class, 'update'])->name('groups.update');
                Route::delete('/groups/{collectionGroup}', [CollectionGroupController::class, 'destroy'])->name('groups.destroy');

                Route::get('/{collection}/edit', [CollectionEditController::class, 'edit'])->name('edit');
                Route::put('/{collection}', [CollectionEditController::class, 'update'])->name('update');
                Route::delete('/{collection}', [CollectionEditController::class, 'destroy'])->name('destroy');
                Route::put('/{collection}/move', [CollectionMoveController::class, 'update'])->name('move');

                Route::patch('/{collection}/draft', [EditDraftController::class, 'update'])->name('draft.update');
                Route::delete('/{collection}/draft', [EditDraftController::class, 'destroy'])->name('draft.destroy');
                Route::post('/{collection}/draft/commit', [EditDraftController::class, 'commit'])->name('draft.commit');

                Route::post('/{collection}/products', [CollectionProductsController::class, 'attach'])->name('products.attach');
                Route::post('/{collection}/products/reorder', [CollectionProductsController::class, 'reorder'])->name('products.reorder');
                Route::delete('/{collection}/products/{product}', [CollectionProductsController::class, 'detach'])->name('products.detach');

                Route::scopeBindings()->group(function (): void {
                    Route::post('/{collection}/urls', [CollectionUrlController::class, 'store'])->name('urls.store');
                    Route::put('/{collection}/urls/{url}', [CollectionUrlController::class, 'update'])->name('urls.update');
                    Route::delete('/{collection}/urls/{url}', [CollectionUrlController::class, 'destroy'])->name('urls.destroy');

                    Route::post('/{collection}/media', [CollectionMediaController::class, 'store'])->name('media.store');
                    Route::post('/{collection}/media/reorder', [CollectionMediaController::class, 'reorder'])->name('media.reorder');
                    Route::put('/{collection}/media/{media}', [CollectionMediaController::class, 'update'])->name('media.update');
                    Route::delete('/{collection}/media/{media}', [CollectionMediaController::class, 'destroy'])->name('media.destroy');
                });
            });
        };
    }
}
