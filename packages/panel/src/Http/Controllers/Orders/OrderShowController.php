<?php

namespace Lunar\Panel\Http\Controllers\Orders;

use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Actions\Fulfilment\CreateFulfilment;
use Lunar\Core\Actions\Fulfilment\ShipFulfilment;
use Lunar\Core\Actions\Orders\CancelOrder;
use Lunar\Core\Actions\Orders\CaptureOrder;
use Lunar\Core\Actions\Orders\RefundOrder;
use Lunar\Core\Contracts\ShippingCarrier;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Facades\CancelReasons;
use Lunar\Core\Facades\Carriers;
use Lunar\Core\Facades\OrderNotifications;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentLine;
use Lunar\Core\Models\FulfilmentTracking;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderAddress;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Transaction;
use Lunar\Panel\Support\TimelineActivity;
use Spatie\Activitylog\Models\Activity;

class OrderShowController
{
    public function show(Order $order): Response
    {
        $order->load([
            'lines',
            'fulfilments.lines.orderLine',
            'fulfilments.trackings',
            'transactions',
            'shippingAddress.country',
            'billingAddress.country',
            'customer',
            'channel',
            'currency',
            'tags',
        ]);

        $currency = $order->currency ?? Currency::getDefault();
        $money = fn (int $minor): ?string => $currency ? (new PriceValue($minor, $currency))->format() : null;
        $factor = (int) ($currency?->factor ?: 100);
        $toMajor = fn (int $minor): float => round($minor / $factor, 2);

        $refunded = (int) $order->transactions->where('type', 'refund')->where('success', true)->sum('amount');
        $availableToRefund = RefundOrder::availableToRefund($order);

        $billing = $order->billingAddress;
        $customerName = $billing
            ? (trim($billing->first_name.' '.$billing->last_name) ?: $billing->company_name)
            : ($order->customer ? trim($order->customer->first_name.' '.$order->customer->last_name) : null);

        return Inertia::render('orders/Show', [
            'order' => [
                'id' => $order->id,
                'reference' => $order->reference ?: '#'.$order->id,
                'customer_reference' => $order->customer_reference,
                'payment_status' => $order->payment_status::$name,
                'payment_status_label' => $order->payment_status->label(),
                'fulfilment_status' => $order->fulfilment_status::$name,
                'fulfilment_status_label' => $order->fulfilment_status->label(),
                'lifecycle' => $order->lifecycleStatus(),
                'lifecycle_label' => __('lunar::states.order.'.$order->lifecycleStatus()),
                'cancelled' => $order->isCancelled(),
                'cancel_reason_label' => $order->isCancelled() ? $order->cancelReasonLabel() : null,
                'cancel_note' => $order->cancel_note,
                'channel' => $order->channel?->name,
                'new_customer' => (bool) $order->new_customer,
                'notes' => $order->notes,
                'meta' => $order->meta,
                'placed_at' => $order->placed_at,
                'created_at' => $order->created_at,
                'closed_at' => $order->closed_at,
                'cancelled_at' => $order->cancelled_at,
            ],
            'lines' => $order->lines
                ->where('type', '!=', 'shipping')
                ->values()
                ->map(fn (OrderLine $line) => [
                    'id' => $line->id,
                    'description' => $line->description,
                    'option' => $line->option,
                    'identifier' => $line->identifier,
                    'quantity' => $line->quantity,
                    'unit_price' => $money($line->unit_price),
                    'total' => $money($line->total),
                ]),
            'shippingLines' => $order->lines
                ->where('type', 'shipping')
                ->values()
                ->map(fn (OrderLine $line) => [
                    'id' => $line->id,
                    'description' => $line->description,
                    'total' => $money($line->total),
                ]),
            'fulfilments' => $order->fulfilments->map(fn (Fulfilment $fulfilment) => [
                'id' => $fulfilment->id,
                'reference' => $fulfilment->reference ?: '#'.$fulfilment->id,
                'state' => $fulfilment->state::$name,
                'state_label' => __('lunar::states.fulfilment.'.$fulfilment->state::$name),
                'method' => $fulfilment->method,
                'shipped_at' => $fulfilment->shipped_at,
                'notes' => $fulfilment->notes,
                'lines' => $fulfilment->lines->map(fn (FulfilmentLine $line) => [
                    'id' => $line->id,
                    'quantity' => $line->quantity,
                    'description' => $line->orderLine?->description,
                    'identifier' => $line->orderLine?->identifier,
                    'option' => $line->orderLine?->option,
                ]),
                'trackings' => $fulfilment->trackings->map(fn (FulfilmentTracking $tracking) => [
                    'carrier' => $tracking->carrier,
                    'tracking_number' => $tracking->tracking_number,
                    'url' => $tracking->url,
                ]),
                'can_ship' => ShipFulfilment::canRun($fulfilment),
                'ship_url' => route('panel.orders.fulfilments.ship', [$order, $fulfilment]),
            ]),
            'transactions' => $order->transactions->map(fn (Transaction $transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'success' => (bool) $transaction->success,
                'driver' => $transaction->driver,
                'amount' => $money($transaction->amount),
                'reference' => $transaction->reference,
                'status' => $transaction->status,
                'card_type' => $transaction->card_type,
                'last_four' => $transaction->last_four,
                'created_at' => $transaction->created_at,
            ]),
            'totals' => [
                'sub_total' => $money($order->sub_total),
                'discount_total' => $order->discount_total ? $money($order->discount_total) : null,
                'shipping_total' => $money($order->shipping_total),
                'tax_total' => $money($order->tax_total),
                'total' => $money($order->total),
                'refunded' => $refunded ? $money($refunded) : null,
                'net' => $refunded ? $money($order->total - $refunded) : null,
            ],
            'customer' => [
                'name' => $customerName ?: null,
                'email' => $billing?->contact_email,
                'new_customer' => (bool) $order->new_customer,
                'url' => $order->customer ? route('panel.customers.edit', $order->customer) : null,
            ],
            'shippingAddress' => $this->address($order->shippingAddress),
            'billingAddress' => $this->address($order->billingAddress),
            'tags' => $order->tags->pluck('value')->all(),
            'actions' => [
                'can_capture' => CaptureOrder::canRun($order),
                'can_refund' => RefundOrder::canRun($order),
                'can_cancel' => CancelOrder::canRun($order),
                'is_open' => $order->isOpen(),
            ],
            'intents' => CaptureOrder::intents($order)->map(fn (Transaction $t) => [
                'id' => $t->id,
                'reference' => $t->reference,
                'amount' => $toMajor($t->amount),
                'amount_formatted' => $money($t->amount),
            ])->values(),
            'charges' => RefundOrder::charges($order)->map(fn (Transaction $t) => [
                'id' => $t->id,
                'reference' => $t->reference,
                'amount' => $toMajor($t->amount),
                'amount_formatted' => $money($t->amount),
            ])->values(),
            'availableToRefund' => $toMajor($availableToRefund),
            'availableToRefundFormatted' => $money($availableToRefund),
            'cancelReasons' => CancelReasons::all(),
            'notifications' => OrderNotifications::sendable(),
            'carriers' => Carriers::all()->mapWithKeys(fn (ShippingCarrier $carrier) => [$carrier->getKey() => $carrier->getName()]),
            'canCreateFulfilment' => CreateFulfilment::canRun($order),
            'activities' => Inertia::defer(fn () => $order->activities()
                ->with('causer')
                ->latest()
                ->limit(25)
                ->get()
                ->map(fn (Activity $activity) => TimelineActivity::toArray($activity))),
            'urls' => [
                'index' => route('panel.orders.index'),
                'capture' => route('panel.orders.capture', $order),
                'refund' => route('panel.orders.refund', $order),
                'cancel' => route('panel.orders.cancel', $order),
                'notify' => route('panel.orders.notify', $order),
                'note' => route('panel.orders.note.update', $order),
                'tags' => route('panel.orders.tags.update', $order),
                'fulfilmentsStore' => route('panel.orders.fulfilments.store', $order),
            ],
        ]);
    }

    /**
     * Flatten an order address for display.
     *
     * @return array<string, mixed>|null
     */
    protected function address(?OrderAddress $address): ?array
    {
        if (! $address) {
            return null;
        }

        return [
            'first_name' => $address->first_name,
            'last_name' => $address->last_name,
            'company_name' => $address->company_name,
            'line_one' => $address->line_one,
            'line_two' => $address->line_two,
            'line_three' => $address->line_three,
            'city' => $address->city,
            'state' => $address->state,
            'postcode' => $address->postcode,
            'country' => $address->country?->name,
            'contact_email' => $address->contact_email,
            'contact_phone' => $address->contact_phone,
            'delivery_instructions' => $address->delivery_instructions,
            'shipping_option' => $address->shipping_option,
        ];
    }
}
