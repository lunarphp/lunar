<?php

use Illuminate\Support\Facades\Route;
use Lunar\Api\Storefront\Http\Controllers\V1\BrandController;
use Lunar\Api\Storefront\Http\Controllers\V1\CartController;
use Lunar\Api\Storefront\Http\Controllers\V1\CartLineController;
use Lunar\Api\Storefront\Http\Controllers\V1\CollectionController;
use Lunar\Api\Storefront\Http\Controllers\V1\CollectionGroupController;
use Lunar\Api\Storefront\Http\Controllers\V1\MeController;
use Lunar\Api\Storefront\Http\Controllers\V1\ProductController;
use Lunar\Api\Storefront\Http\Middleware\ResolveCustomer;

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/{id}', [ProductController::class, 'show'])->name('products.show');

Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
Route::get('brands/{id}', [BrandController::class, 'show'])->name('brands.show');

Route::get('collections', [CollectionController::class, 'index'])->name('collections.index');
Route::get('collections/{id}', [CollectionController::class, 'show'])->name('collections.show');

Route::get('collection-groups', [CollectionGroupController::class, 'index'])->name('collection-groups.index');
Route::get('collection-groups/{id}', [CollectionGroupController::class, 'show'])->name('collection-groups.show');

Route::get('cart', [CartController::class, 'current'])->name('carts.show');
Route::post('cart/lines', [CartLineController::class, 'store'])->name('carts.lines.store');

if (config('lunar.api.storefront.guard')) {
    Route::middleware(ResolveCustomer::class)->group(function (): void {
        Route::get('me', [MeController::class, 'me'])->name('customers.me');
    });
}
