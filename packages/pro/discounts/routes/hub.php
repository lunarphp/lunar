<?php

use GetCandy\Discounts\Http\Livewire\DiscountShow;
use GetCandy\Discounts\Http\Livewire\DiscountsIndex;
use Illuminate\Support\Facades\Route;
use GetCandy\Hub\Http\Middleware\Authenticate;

Route::group([
  'prefix'     => config('getcandy-hub.system.path', 'hub'),
  'middleware' => ['web'],
], function () {
    Route::group([
        'middleware' => [
            Authenticate::class,
        ],
        'prefix' => 'discounts',
    ], function () {

        Route::get('/', DiscountsIndex::class)->name('hub.discounts.index');
        Route::get('{discount}', DiscountShow::class)->name('hub.discounts.show');
        // Route::get('shipping-zones/{id}', ShippingZone::class)->name('hub.shipping.zone');
    });

});
