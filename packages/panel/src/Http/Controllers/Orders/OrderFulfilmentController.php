<?php

namespace Lunar\Panel\Http\Controllers\Orders;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Actions\Fulfilment\CreateFulfilment;
use Lunar\Core\Actions\Fulfilment\ShipFulfilment;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Panel\Http\Requests\Orders\ShipFulfilmentRequest;

class OrderFulfilmentController
{
    /**
     * Create a fulfilment covering every outstanding fulfillable quantity on
     * the order. Split/merge and partial allocation are a later slice.
     */
    public function store(Order $order): RedirectResponse
    {
        abort_unless(CreateFulfilment::canRun($order), 403);

        $lines = $order->fulfillableLines()
            ->with('fulfilmentLines')
            ->get()
            ->mapWithKeys(fn (OrderLine $line) => [
                $line->id => $line->quantity - (int) $line->fulfilmentLines->sum('quantity'),
            ])
            ->filter(fn (int $outstanding) => $outstanding > 0)
            ->all();

        if ($lines === []) {
            return back()->with('error', __('panel::orders.fulfilment_nothing_outstanding'));
        }

        try {
            $order->createFulfilment($lines);
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('panel::orders.flash_fulfilment_created'));
    }

    public function ship(ShipFulfilmentRequest $request, Order $order, Fulfilment $fulfilment): RedirectResponse
    {
        abort_unless(ShipFulfilment::canRun($fulfilment), 403);

        $entry = array_filter([
            'carrier' => $request->input('carrier'),
            'shipping_method' => $request->input('shipping_method'),
            'tracking_number' => $request->input('tracking_number'),
            'tracking_url' => $request->input('tracking_url'),
        ], fn ($value) => $value !== null && $value !== '');

        try {
            $fulfilment->ship($entry, $request->boolean('notify', true));
        } catch (FulfilmentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('panel::orders.flash_fulfilment_shipped'));
    }
}
