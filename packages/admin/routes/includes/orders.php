<?php

use Barryvdh\DomPDF\Facade\Pdf;
use Lunar\Hub\Http\Livewire\Pages\Orders\OrderShow;
use Lunar\Hub\Http\Livewire\Pages\Orders\OrdersIndex;
use Lunar\Models\Order;
use Illuminate\Http\Request;
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

        Route::get('/pdf', function (Order $order, Request $request) {
            return Pdf::loadView('adminhub::pdf.order', [
                'order' => $order,
            ])->stream("Order-{$order->reference}.pdf");
        })->name('hub.orders.pdf');
    });
});
