<?php

use Illuminate\Support\Facades\Route;
use Lunar\Checkout\Http\Controllers\CheckoutController;

Route::middleware(config('lunar.checkout.middleware', ['web']))
    ->group(function () {
        Route::get(config('lunar.checkout.path', 'checkout'), [CheckoutController::class, 'show'])
            ->name('lunar.checkout.show');
    });
