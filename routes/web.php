<?php

use GetCandy\Hub\Http\Livewire\Hub;
use GetCandy\Hub\Http\Livewire\Pages\Authentication\Login;
use GetCandy\Hub\Http\Livewire\Pages\Authentication\PasswordReset;
use GetCandy\Hub\Http\Middleware\Authenticate;
use GetCandy\Hub\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix'     => 'hub',
    'middleware' => ['web'],
], function () {
    Route::post('logout', function () {
        Auth::guard('staff')->logout();

        return redirect()->route('hub.login');
    })->name('hub.logout');

    Route::group([
        'middleware' => RedirectIfAuthenticated::class,
    ], function ($router) {
        $router->get('login', Login::class)->name('hub.login');
        $router->get('password-reset/{hash?}', PasswordReset::class)->name('hub.password-reset');
    });

    Route::group([
        'middleware' => [
            Authenticate::class,
        ],
    ], function ($router) {
        $router->get('/', Hub::class)->name('hub.index');

        Route::group([
            'prefix' => 'products',
        ], __DIR__.'/includes/products.php');

        Route::group([
            'prefix' => 'product-types',
        ], __DIR__.'/includes/product-types.php');

        Route::group([
            'prefix' => 'orders',
        ], __DIR__.'/includes/orders.php');

        Route::group([], __DIR__.'/includes/collections.php');

        Route::group([
            'prefix' => 'settings',
        ], __DIR__.'/includes/settings.php');

        Route::group([
            'prefix' => 'customers',
        ], __DIR__.'/includes/customers.php');
    });
});
