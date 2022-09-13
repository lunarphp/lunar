<?php

use Lunar\Hub\Http\Livewire\Pages\Customers\CustomerShow;
use Lunar\Hub\Http\Livewire\Pages\Customers\CustomersIndex;
use Illuminate\Support\Facades\Route;

/**
 * Channel routes.
 */
Route::group([
    'middleware' => 'can:catalogue:manage-customers',
], function () {
    Route::get('/', CustomersIndex::class)->name('hub.customers.index');

    Route::group([
        'prefix' => '{customer}',
    ], function () {
        Route::get('/', CustomerShow::class)->name('hub.customers.show');
    });
});
