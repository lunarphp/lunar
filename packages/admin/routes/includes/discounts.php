<?php

use Illuminate\Support\Facades\Route;
use Lunar\Hub\Http\Livewire\Pages\Discounts\DiscountCreate;
use Lunar\Hub\Http\Livewire\Pages\Discounts\DiscountShow;
use Lunar\Hub\Http\Livewire\Pages\Discounts\DiscountsIndex;

/**
 * Channel routes.
 */
Route::group([
    'middleware' => 'can:catalogue:manage-discounts',
], function () {
    Route::get('/', DiscountsIndex::class)->name('hub.discounts.index');
    Route::get('create', DiscountCreate::class)->name('hub.discounts.create');
    Route::get('{discount}', DiscountShow::class)->name('hub.discounts.show');
});
