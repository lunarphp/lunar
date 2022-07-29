<?php

use Barryvdh\DomPDF\Facade\Pdf;
use GetCandy\Hub\Http\Livewire\Pages\Orders\OrderShow;
use GetCandy\Hub\Http\Livewire\Pages\Orders\OrdersIndex;
use GetCandy\Models\Order;
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
            return view('adminhub::pdf.order', [
                'order' => $order,
            ]);
            $pdf = Pdf::loadView('adminhub::pdf.order', [
                'order' => $order,
            ]);

            return $pdf->stream("Order-{$order->reference}.pdf");
        })->name('hub.orders.pdf');
    });
});
