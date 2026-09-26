<?php

use Illuminate\Support\Facades\Route;
use Lunar\Api\Admin\Auth\Abilities;
use Lunar\Api\Admin\Http\Controllers\V1\ApiKeyController;
use Lunar\Api\Admin\Http\Controllers\V1\ProductController;

Route::middleware('lunar.api.can:'.Abilities::CATALOG_READ)->group(function (): void {
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{id}', [ProductController::class, 'show'])->name('products.show');
});

Route::middleware('lunar.api.can:'.Abilities::MANAGE_API_KEYS)->group(function (): void {
    Route::get('api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::get('api-keys/{id}', [ApiKeyController::class, 'show'])->name('api-keys.show');
    Route::delete('api-keys/{id}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
});
