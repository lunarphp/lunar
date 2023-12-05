<?php

use Illuminate\Http\Request;

Route::group([
    'prefix' => 'api/paypal',
    'middleware' => ['web'],
], function ($router) {
    $router->post('order', \Lunar\Paypal\Http\Controllers\GetPaypalOrderController::class)->name('post.paypal.order');
});
