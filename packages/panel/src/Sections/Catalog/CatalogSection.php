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
use Lunar\Panel\Http\Controllers\Catalog\ProductOptionSearchController;
use Lunar\Panel\Http\Controllers\Catalog\ProductSearchController;
use Lunar\Panel\Http\Controllers\Collections\CollectionChildrenController;
use Lunar\Panel\Http\Controllers\Collections\CollectionCreateController;
use Lunar\Panel\Http\Controllers\Collections\CollectionEditController;
use Lunar\Panel\Http\Controllers\Collections\CollectionGroupController;
use Lunar\Panel\Http\Controllers\Collections\CollectionIndexController;
use Lunar\Panel\Http\Controllers\Collections\CollectionMediaController;
use Lunar\Panel\Http\Controllers\Collections\CollectionMoveController;
use Lunar\Panel\Http\Controllers\Collections\CollectionProductsController;
use Lunar\Panel\Http\Controllers\Collections\CollectionUrlController;
use Lunar\Panel\Http\Controllers\EditDraftController;
use Lunar\Panel\Http\Controllers\Products\ProductAssociationController;
use Lunar\Panel\Http\Controllers\Products\ProductBulkStatusController;
use Lunar\Panel\Http\Controllers\Products\ProductCreateController;
use Lunar\Panel\Http\Controllers\Products\ProductEditController;
use Lunar\Panel\Http\Controllers\Products\ProductIndexController;
use Lunar\Panel\Http\Controllers\Products\ProductMediaController;
use Lunar\Panel\Http\Controllers\Products\ProductOptionsController;
use Lunar\Panel\Http\Controllers\Products\ProductPriceController;
use Lunar\Panel\Http\Controllers\Products\ProductUrlController;
use Lunar\Panel\Http\Controllers\Products\ProductVariantBulkController;
use Lunar\Panel\Http\Controllers\Products\ProductVariantController;
use Lunar\Panel\Http\Controllers\Products\ProductVariantMediaController;
use Lunar\Panel\Http\Controllers\Products\ProductVariantStockController;
use Lunar\Panel\Http\Controllers\ProductTypes\ProductTypeBulkStatusController;
use Lunar\Panel\Http\Controllers\ProductTypes\ProductTypeCreateController;
use Lunar\Panel\Http\Controllers\ProductTypes\ProductTypeEditController;
use Lunar\Panel\Http\Controllers\ProductTypes\ProductTypeIndexController;
use Lunar\Panel\Http\Controllers\ProductTypes\ProductTypeMediaController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Search\Commands\CreateBrandCommand;
use Lunar\Panel\Search\Commands\CreateCollectionCommand;
use Lunar\Panel\Search\Commands\CreateProductCommand;
use Lunar\Panel\Search\Commands\CreateProductTypeCommand;
use Lunar\Panel\Search\SearchCommand;
use Lunar\Panel\Search\SearchSource;
use Lunar\Panel\Search\Sources\BrandSearchSource;
use Lunar\Panel\Search\Sources\CollectionSearchSource;
use Lunar\Panel\Search\Sources\ProductSearchSource;
use Lunar\Panel\Sections\Catalog\Tables\BrandsTableExtension;
use Lunar\Panel\Sections\Catalog\Tables\CollectionsTableExtension;
use Lunar\Panel\Sections\Catalog\Tables\ProductsTableExtension;
use Lunar\Panel\Sections\Catalog\Tables\ProductTypesTableExtension;
use Lunar\Panel\Sections\Section;

class CatalogSection extends Section
{
    /**
     * Manifest permission handles gating both the routes (via can: middleware)
     * and the navigation items, so what a user can see and what they can reach
     * stay in lockstep. Same handles as the Filament admin's resources.
     */
    public const PRODUCTS_PERMISSION = 'catalog:manage-products';

    public const BRANDS_PERMISSION = 'catalog:manage-products';

    public const COLLECTIONS_PERMISSION = 'catalog:manage-collections';

    public const PRODUCT_TYPES_PERMISSION = 'catalog:manage-products';

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
        // Products leads the group and carries Product types as a contextual
        // child (rendered while a products/product-types page is active) —
        // the design prototype's sub-nav; types are schema-for-products, not
        // a peer resource.
        $registry->addItem('catalog', new NavigationItem(
            key: 'products',
            label: __('panel::nav.products'),
            icon: 'box',
            route: 'panel.products.index',
            permission: self::PRODUCTS_PERMISSION,
            children: [
                new NavigationItem(
                    key: 'all-products',
                    label: __('panel::nav.all_products'),
                    route: 'panel.products.index',
                    permission: self::PRODUCTS_PERMISSION,
                    priority: 10,
                    exact: true,
                ),
                new NavigationItem(
                    key: 'product-types',
                    label: __('panel::nav.product_types'),
                    route: 'panel.product-types.index',
                    permission: self::PRODUCT_TYPES_PERMISSION,
                    priority: 20,
                ),
            ],
        ));
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
            'products.index' => ProductsTableExtension::class,
            'brands.index' => BrandsTableExtension::class,
            'collections.index' => CollectionsTableExtension::class,
            'product-types.index' => ProductTypesTableExtension::class,
        ];
    }

    /** @return array<int, class-string<DraftableResource>> */
    public function draftables(): array
    {
        return [
            BrandDraftResource::class,
            CollectionDraftResource::class,
            ProductDraftResource::class,
            ProductVariantDraftResource::class,
            ProductTypeDraftResource::class,
        ];
    }

    /** @return array<int, class-string<SearchSource>> */
    public function searchSources(): array
    {
        return [
            ProductSearchSource::class,
            CollectionSearchSource::class,
            BrandSearchSource::class,
        ];
    }

    /** @return array<int, class-string<SearchCommand>> */
    public function searchCommands(): array
    {
        return [
            CreateProductCommand::class,
            CreateCollectionCommand::class,
            CreateBrandCommand::class,
            CreateProductTypeCommand::class,
        ];
    }

    /** @return array<string, array<int, class-string>> */
    public function pageActions(): array
    {
        return [
            'products.edit' => [
                DuplicateProductPageAction::class,
            ],
        ];
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('catalog')->name('panel.catalog.')->middleware('can:'.self::BRANDS_PERMISSION)->group(function (): void {
                Route::get('/collections/search', [CollectionSearchController::class, 'search'])->name('collections.search');
                Route::get('/products/search', [ProductSearchController::class, 'search'])->name('products.search');
                Route::get('/product-options/search', [ProductOptionSearchController::class, 'search'])->name('product-options.search');
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

            Route::prefix('products')->name('panel.products.')->middleware('can:'.self::PRODUCTS_PERMISSION)->group(function (): void {
                Route::get('/', [ProductIndexController::class, 'index'])->name('index');
                Route::get('/create', [ProductCreateController::class, 'create'])->name('create');
                Route::post('/', [ProductCreateController::class, 'store'])->name('store');
                Route::post('/bulk/status/{status}', [ProductBulkStatusController::class, 'update'])
                    ->whereIn('status', ['published', 'draft'])
                    ->name('bulk-status');
                Route::get('/{product}/edit', [ProductEditController::class, 'edit'])->name('edit');
                Route::put('/{product}', [ProductEditController::class, 'update'])->name('update');
                Route::delete('/{product}', [ProductEditController::class, 'destroy'])->name('destroy');
                Route::post('/{product}/duplicate', [ProductEditController::class, 'duplicate'])->name('duplicate');

                Route::patch('/{product}/draft', [EditDraftController::class, 'update'])->name('draft.update');
                Route::delete('/{product}/draft', [EditDraftController::class, 'destroy'])->name('draft.destroy');
                Route::post('/{product}/draft/commit', [EditDraftController::class, 'commit'])->name('draft.commit');

                Route::scopeBindings()->group(function (): void {
                    Route::post('/{product}/urls', [ProductUrlController::class, 'store'])->name('urls.store');
                    Route::put('/{product}/urls/{url}', [ProductUrlController::class, 'update'])->name('urls.update');
                    Route::delete('/{product}/urls/{url}', [ProductUrlController::class, 'destroy'])->name('urls.destroy');

                    Route::post('/{product}/media', [ProductMediaController::class, 'store'])->name('media.store');
                    Route::post('/{product}/media/reorder', [ProductMediaController::class, 'reorder'])->name('media.reorder');
                    Route::put('/{product}/media/{media}', [ProductMediaController::class, 'update'])->name('media.update');
                    Route::delete('/{product}/media/{media}', [ProductMediaController::class, 'destroy'])->name('media.destroy');

                    Route::post('/{product}/associations', [ProductAssociationController::class, 'store'])->name('associations.store');
                    Route::post('/{product}/associations/reorder', [ProductAssociationController::class, 'reorder'])->name('associations.reorder');
                    Route::delete('/{product}/associations/{association}', [ProductAssociationController::class, 'destroy'])->name('associations.destroy');

                    Route::post('/{product}/variants/{productVariant}/prices', [ProductPriceController::class, 'store'])->name('variants.prices.store');
                    Route::put('/{product}/variants/{productVariant}/prices/{price}', [ProductPriceController::class, 'update'])->name('variants.prices.update');
                    Route::delete('/{product}/variants/{productVariant}/prices/{price}', [ProductPriceController::class, 'destroy'])->name('variants.prices.destroy');

                    Route::post('/{product}/variants/{productVariant}/stock', [ProductVariantStockController::class, 'update'])->name('variants.stock.adjust');

                    Route::post('/{product}/options/generate', [ProductOptionsController::class, 'generate'])->name('options.generate');
                    Route::post('/{product}/variants/bulk', [ProductVariantBulkController::class, 'update'])->name('variants.bulk');

                    Route::get('/{product}/variants/{productVariant}/edit', [ProductVariantController::class, 'edit'])->name('variants.edit');
                    Route::put('/{product}/variants/{productVariant}', [ProductVariantController::class, 'update'])->name('variants.update');
                    Route::delete('/{product}/variants/{productVariant}', [ProductVariantController::class, 'destroy'])->name('variants.destroy');

                    Route::patch('/{product}/variants/{productVariant}/draft', [EditDraftController::class, 'update'])->name('variants.draft.update');
                    Route::delete('/{product}/variants/{productVariant}/draft', [EditDraftController::class, 'destroy'])->name('variants.draft.destroy');
                    Route::post('/{product}/variants/{productVariant}/draft/commit', [EditDraftController::class, 'commit'])->name('variants.draft.commit');

                    Route::put('/{product}/variants/{productVariant}/media', [ProductVariantMediaController::class, 'sync'])->name('variants.media.sync');
                });
            });

            Route::prefix('product-types')->name('panel.product-types.')->middleware('can:'.self::PRODUCT_TYPES_PERMISSION)->group(function (): void {
                Route::get('/', [ProductTypeIndexController::class, 'index'])->name('index');
                Route::get('/create', [ProductTypeCreateController::class, 'create'])->name('create');
                Route::post('/', [ProductTypeCreateController::class, 'store'])->name('store');
                Route::post('/bulk/status/{status}', [ProductTypeBulkStatusController::class, 'update'])
                    ->whereIn('status', ['active', 'draft'])
                    ->name('bulk-status');
                Route::get('/{productType}/edit', [ProductTypeEditController::class, 'edit'])->name('edit');
                Route::put('/{productType}', [ProductTypeEditController::class, 'update'])->name('update');
                Route::delete('/{productType}', [ProductTypeEditController::class, 'destroy'])->name('destroy');

                Route::patch('/{productType}/draft', [EditDraftController::class, 'update'])->name('draft.update');
                Route::delete('/{productType}/draft', [EditDraftController::class, 'destroy'])->name('draft.destroy');
                Route::post('/{productType}/draft/commit', [EditDraftController::class, 'commit'])->name('draft.commit');

                Route::scopeBindings()->group(function (): void {
                    Route::post('/{productType}/media', [ProductTypeMediaController::class, 'store'])->name('media.store');
                    Route::post('/{productType}/media/reorder', [ProductTypeMediaController::class, 'reorder'])->name('media.reorder');
                    Route::put('/{productType}/media/{media}', [ProductTypeMediaController::class, 'update'])->name('media.update');
                    Route::delete('/{productType}/media/{media}', [ProductTypeMediaController::class, 'destroy'])->name('media.destroy');
                });
            });

            Route::prefix('collections')->name('panel.collections.')->middleware('can:'.self::COLLECTIONS_PERMISSION)->group(function (): void {
                Route::get('/', [CollectionIndexController::class, 'index'])->name('index');
                Route::get('/create', [CollectionCreateController::class, 'create'])->name('create');
                Route::post('/', [CollectionCreateController::class, 'store'])->name('store');

                Route::post('/groups', [CollectionGroupController::class, 'store'])->name('groups.store');
                Route::put('/groups/{collectionGroup}', [CollectionGroupController::class, 'update'])->name('groups.update');
                Route::delete('/groups/{collectionGroup}', [CollectionGroupController::class, 'destroy'])->name('groups.destroy');

                Route::get('/{collection}/children', [CollectionChildrenController::class, 'index'])->name('children');

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
