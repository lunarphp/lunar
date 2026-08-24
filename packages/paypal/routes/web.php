<?php

use Illuminate\Support\Facades\Route;
use Lunar\Paypal\Http\Controllers\GetPaypalOrderController;

Route::group([
    'prefix' => 'api/paypal',
    'middleware' => ['web'],
], function ($router) {
    $router->post('order', GetPaypalOrderController::class)->name('post.paypal.order');
});
