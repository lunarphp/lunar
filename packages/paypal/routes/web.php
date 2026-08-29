<?php

use Illuminate\Support\Facades\Route;
use Lunar\Paypal\Http\Controllers\GetPaypalOrderController;

Route::group([
    'prefix' => 'api/paypal',
    'middleware' => ['web', 'throttle:'.config('lunar.paypal.order_rate_limit', '10,1')],
], function ($router) {
    $router->post('order', GetPaypalOrderController::class)->name('post.paypal.order');
});
