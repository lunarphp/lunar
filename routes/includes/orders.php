<?php

use GetCandy\Hub\Http\Livewire\Pages\Orders\OrderShow;
use GetCandy\Hub\Http\Livewire\Pages\Orders\OrdersIndex;
use Illuminate\Support\Facades\Route;

/**
 * Channel routes.
 */
Route::group([
    'middleware' => 'can:catalogue:manage-orders',
], function () {
    Route::get('/', OrdersIndex::class)->name('hub.orders.index');

    Route::group([
        'prefix' => '{order}',
    ], function () {
        Route::get('/', OrderShow::class)->name('hub.orders.show');
    });
});
