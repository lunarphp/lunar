<?php

use Illuminate\Support\Facades\Route;
use Lunar\Hub\Http\Livewire\Pages\Discounts\DiscountCreate;
use Lunar\Hub\Http\Livewire\Pages\Discounts\DiscountShow;
use Lunar\Hub\Http\Livewire\Pages\Discounts\DiscountsIndex;

/**
 * Channel routes.
 */
Route::group([
    // 'middleware' => 'can:catalogue:manage-discounts',
], function () {
    Route::get('/', DiscountsIndex::class)->name('hub.discounts.index');
    Route::get('create', DiscountCreate::class)->name('hub.discounts.create');
    Route::get('{discount}', DiscountShow::class)->name('hub.discounts.show');

//     Route::get('create', ProductCreate::class)->name('hub.products.create');
//
//     Route::group([
//         'prefix' => '{product}',
//     ], function () {
//         Route::get('/', ProductShow::class)->name('hub.products.show');
//
//         Route::group([
//             'prefix' => 'variants',
//         ], function () {
//             Route::get('{variant}', VariantShow::class)->name('hub.products.variants.show');
//         });
//     });
});
