<?php

namespace Lunar\Hub\Exporters;

use Illuminate\Support\Facades\Storage;
use Lunar\Models\Order;

class OrderExporter
{
    /**
     * Export the orders.
     *
     * @param  array  $orderIds
     * @return void
     */
    public function export($orderIds)
    {
        $data = [$this->getHeadings()];

        $orders = Order::findMany($orderIds)->map(function ($order) {
            return collect([
                $order->id,
                $order->status,
                $order->reference,
                $order->billingAddress->full_name,
                $order->total->decimal,
                $order->created_at->format('Y-m-d'),
                $order->created_at->format('H:ma'),
            ])->join(',');
        })->toArray();

        $data = collect(array_merge($data, $orders))->join("\n");

        Storage::put('order_export.csv', $data);

        return Storage::download('order_export.csv');
    }

    /**
     * Return the csv headings.
     *
     * @return string
     */
    public function getHeadings()
    {
        return collect([
            'ID',
            'Status',
            'Reference',
            'Customer',
            'Total',
            'Date',
            'Time',
        ])->join(',');
    }
}
