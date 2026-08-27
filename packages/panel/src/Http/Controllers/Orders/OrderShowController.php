<?php

namespace Lunar\Panel\Http\Controllers\Orders;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Actions\Fulfilment\ChangeFulfilmentLocation;
use Lunar\Core\Actions\Fulfilment\HoldFulfilment;
use Lunar\Core\Actions\Fulfilment\MergeFulfilments;
use Lunar\Core\Actions\Fulfilment\ReleaseFulfilment;
use Lunar\Core\Actions\Fulfilment\SplitFulfilment;
use Lunar\Core\Actions\Orders\CancelOrder;
use Lunar\Core\Actions\Orders\CaptureOrder;
use Lunar\Core\Actions\Orders\RefundOrder;
use Lunar\Core\Contracts\ShippingCarrier;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Enums\FulfilmentStateCategory;
use Lunar\Core\Facades\CancelReasons;
use Lunar\Core\Facades\Carriers;
use Lunar\Core\Facades\HoldReasons;
use Lunar\Core\Facades\OrderNotifications;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentLine;
use Lunar\Core\Models\FulfilmentTracking;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderAddress;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\RefundLine;
use Lunar\Core\Models\Transaction;
use Lunar\Core\ValueObjects\Cart\TaxBreakdownAmount;
use Lunar\Panel\Support\FulfilmentTransitions;
use Lunar\Panel\Support\TimelineActivity;
use Spatie\Activitylog\Models\Activity;

class OrderShowController
{
    public function show(Order $order): Response
    {
        $order->load([
            'lines.purchasable',
            'lines.fulfilmentLines',
            'fulfilments.lines.orderLine.purchasable',
            'fulfilments.trackings',
            'fulfilments.location',
            'transactions.refundLines.orderLine',
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

        $captured = (int) $order->transactions->where('type', 'capture')->where('success', true)->sum('amount');
        $refunded = (int) $order->transactions->where('type', 'refund')->where('success', true)->sum('amount');
        $availableToRefund = RefundOrder::availableToRefund($order);
        $settlement = $this->settlement($order, $captured, $refunded, $money, $toMajor);

        $billing = $order->billingAddress;
        $customerName = $billing
            ? (trim($billing->first_name.' '.$billing->last_name) ?: $billing->company_name)
            : ($order->customer ? trim($order->customer->first_name.' '.$order->customer->last_name) : null);

        $locations = Location::query()->orderBy('name')->get();
        $shippingOption = $this->shippingOption($order, $money);

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
            'fulfilments' => $order->fulfilments->map(
                fn (Fulfilment $fulfilment) => $this->fulfilment($order, $fulfilment, $locations, $shippingOption, $money)
            ),
            // Non-shipping lines with no fulfilment allocation — services and
            // other non-fulfillable purchasables. Fulfillable lines always live
            // in a fulfilment (created at placement, split/merged after).
            'otherLines' => $order->lines
                ->filter(fn (OrderLine $line) => $line->type !== 'shipping' && $line->fulfilmentLines->isEmpty())
                ->values()
                ->map(fn (OrderLine $line) => $this->line($line, $line->quantity, $money)),
            // Every non-shipping line with quantity still left to refund —
            // the refund composer's line picker.
            'refundableLines' => $order->lines
                ->filter(fn (OrderLine $line) => $line->type !== 'shipping' && $line->refundableQuantity() > 0)
                ->values()
                ->map(fn (OrderLine $line) => [
                    ...$this->line($line, $line->quantity, $money),
                    'refundable_quantity' => $line->refundableQuantity(),
                    'refund_unit_amount' => $toMajor((int) round($line->total / max(1, $line->quantity))),
                ]),
            'shippingLines' => $order->lines
                ->where('type', 'shipping')
                ->values()
                ->map(fn (OrderLine $line) => [
                    'id' => $line->id,
                    'description' => $line->description,
                    'total' => $money($line->total),
                    'amount' => $toMajor($line->total),
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
                'lines_summary' => $this->refundLinesSummary($transaction),
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
            'settlement' => $settlement,
            'customer' => [
                'name' => $customerName ?: null,
                'email' => $billing?->contact_email,
                'new_customer' => (bool) $order->new_customer,
                'url' => $order->customer ? route('panel.customers.edit', $order->customer) : null,
            ],
            'shippingAddress' => $this->address($order->shippingAddress, $order),
            'billingAddress' => $this->address($order->billingAddress, $order),
            'shippingOption' => $shippingOption,
            'countries' => Country::orderBy('name')->get(['id', 'name']),
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
            'carriers' => Carriers::all()->map(fn (ShippingCarrier $carrier) => [
                'key' => $carrier->getKey(),
                'name' => $carrier->getName(),
                'services' => collect($carrier->getServices())->map(fn (string $label) => __($label))->all(),
            ])->values(),
            'holdReasons' => HoldReasons::all(),
            'locations' => $locations->map(fn (Location $location) => [
                'id' => $location->id,
                'name' => $location->name,
            ])->values(),
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
            ],
        ]);
    }

    /**
     * The full card payload for one fulfilment: chrome (state, method, hold,
     * location, handed-over), allocated lines with price detail, tracking rows,
     * the offered status transitions, per-action gates, merge candidates, and
     * the endpoint URL map.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Location>  $locations
     * @param  array{name: string, identifier: ?string, price: ?string}|null  $shippingOption
     * @return array<string, mixed>
     */
    protected function fulfilment(Order $order, Fulfilment $fulfilment, \Illuminate\Database\Eloquent\Collection $locations, ?array $shippingOption, callable $money): array
    {
        $method = $fulfilment->method();
        $category = $fulfilment->state->category();
        $mergeTargets = $this->mergeTargets($order, $fulfilment);

        return [
            'id' => $fulfilment->id,
            'reference' => $fulfilment->reference ?: '#'.$fulfilment->id,
            'method' => $fulfilment->method,
            'method_label' => $method->getLabel(),
            'state' => $fulfilment->state::$name,
            'state_label' => __('lunar::states.fulfilment.'.$fulfilment->state::$name),
            'state_category' => strtolower($category->name),
            'on_hold' => $fulfilment->isOnHold(),
            'hold_reason_label' => $fulfilment->isOnHold() ? $fulfilment->holdReasonLabel() : null,
            'hold_note' => $fulfilment->isOnHold() ? $fulfilment->hold_note : null,
            'location' => $fulfilment->location?->name,
            'location_id' => $fulfilment->location_id,
            'shipped_at' => $fulfilment->shipped_at,
            'handed_over_label' => $this->methodLabel('handed_over', $fulfilment->method, 'handed_over_default'),
            'fulfil_label' => $this->methodLabel('fulfil_label', $fulfilment->method, 'fulfil_label'),
            // The checkout's delivery method, surfaced on dispatchable parcels
            // so the admin knows which service the customer paid for.
            'delivery_method' => $method->usesTracking() ? ($shippingOption['name'] ?? null) : null,
            'notes' => $fulfilment->notes,
            'lines' => $fulfilment->lines->map(fn (FulfilmentLine $line) => [
                ...$this->line($line->orderLine, $line->quantity, $money),
                'id' => $line->id,
                'order_line_id' => $line->order_line_id,
            ]),
            'trackings' => $fulfilment->trackings->map(fn (FulfilmentTracking $tracking) => [
                'id' => $tracking->id,
                'carrier' => $tracking->carrier,
                'carrier_name' => $tracking->carrier()?->getName() ?? $tracking->carrier,
                'shipping_method' => $tracking->shippingMethodLabel(),
                'tracking_number' => $tracking->tracking_number,
                'url' => $tracking->url,
                'destroy_url' => route('panel.orders.fulfilments.trackings.destroy', [$order, $fulfilment, $tracking]),
            ]),
            'transitions' => FulfilmentTransitions::for($fulfilment)
                ->map(fn (array $transition) => [
                    'state' => $transition['name'],
                    'label' => $transition['label'],
                    'via' => $transition['via'],
                    'notify' => $transition['notify'],
                ]),
            'can' => [
                'split' => SplitFulfilment::canRun($fulfilment) && $fulfilment->lines->sum('quantity') > 1,
                'merge' => MergeFulfilments::isMergeable($fulfilment) && $mergeTargets->isNotEmpty(),
                'change_location' => ChangeFulfilmentLocation::canRun($fulfilment) && $locations->count() > 1,
                'add_tracking' => $method->usesTracking() && $category === FulfilmentStateCategory::Fulfilled,
                'undo_return' => $category === FulfilmentStateCategory::Returned
                    && $fulfilment->state->canTransitionTo($method->fulfilledState()),
                'hold' => HoldFulfilment::canRun($fulfilment),
                'release' => ReleaseFulfilment::canRun($fulfilment),
                'cancel' => $fulfilment->state->canTransitionTo($method->defaultState()),
            ],
            'merge_targets' => $mergeTargets->map(fn (Fulfilment $target) => [
                'id' => $target->id,
                'reference' => $target->reference ?: '#'.$target->id,
                'quantity' => (int) $target->lines->sum('quantity'),
            ]),
            'urls' => [
                'ship' => route('panel.orders.fulfilments.ship', [$order, $fulfilment]),
                'fulfil' => route('panel.orders.fulfilments.fulfil', [$order, $fulfilment]),
                'transition' => route('panel.orders.fulfilments.transition', [$order, $fulfilment]),
                'split' => route('panel.orders.fulfilments.split', [$order, $fulfilment]),
                'merge' => route('panel.orders.fulfilments.merge', [$order, $fulfilment]),
                'return' => route('panel.orders.fulfilments.return', [$order, $fulfilment]),
                'undoReturn' => route('panel.orders.fulfilments.undo-return', [$order, $fulfilment]),
                'hold' => route('panel.orders.fulfilments.hold', [$order, $fulfilment]),
                'release' => route('panel.orders.fulfilments.release', [$order, $fulfilment]),
                'cancel' => route('panel.orders.fulfilments.cancel', [$order, $fulfilment]),
                'location' => route('panel.orders.fulfilments.location.update', [$order, $fulfilment]),
                'trackings' => route('panel.orders.fulfilments.trackings.store', [$order, $fulfilment]),
            ],
        ];
    }

    /**
     * "2× Widget, 1× Gadget" from a refund transaction's recorded line
     * allocations — null for non-refund transactions or refunds with no
     * tracked allocation (a driver that didn't hand back a transaction, or an
     * amount-only refund with nothing but a manual adjustment).
     */
    protected function refundLinesSummary(Transaction $transaction): ?string
    {
        if ($transaction->type !== 'refund' || $transaction->refundLines->isEmpty()) {
            return null;
        }

        $parts = $transaction->refundLines
            ->map(fn (RefundLine $refundLine) => $refundLine->quantity.'× '.($refundLine->orderLine?->description ?? '—'))
            ->all();

        $lineAmount = (int) $transaction->refundLines->sum('amount');

        if ($lineAmount < $transaction->amount) {
            $parts[] = __('panel::orders.refund_summary_and_more');
        }

        return implode(', ', $parts);
    }

    /**
     * A line row shared by fulfilment cards and the "other items" section —
     * identity, thumbnail, and the expandable price detail. `quantity` is the
     * quantity shown on the row (a fulfilment's allocation, or the line's own).
     *
     * @return array<string, mixed>
     */
    protected function line(?OrderLine $line, int $quantity, callable $money): array
    {
        if (! $line) {
            return [];
        }

        return [
            'id' => $line->id,
            'quantity' => $quantity,
            'line_quantity' => $line->quantity,
            'description' => $line->description,
            'option' => $line->option,
            'identifier' => $line->identifier,
            'thumbnail' => $line->purchasable?->getThumbnail()?->getUrl('small'),
            'unit_price' => $money($line->unit_price),
            'sub_total' => $money($line->sub_total),
            'discount_total' => $line->discount_total ? $money($line->discount_total) : null,
            'tax' => collect($line->tax_breakdown?->amounts ?? [])->map(fn (TaxBreakdownAmount $tax) => [
                'label' => $tax->description,
                'amount' => $tax->price->format(),
            ])->values()->all(),
            'total' => $money($line->total),
            'notes' => $line->notes,
        ];
    }

    /**
     * Other outstanding fulfilments the given one could merge into — same
     * order, location, and method (mirrors the core merge guards).
     *
     * @return Collection<int, Fulfilment>
     */
    protected function mergeTargets(Order $order, Fulfilment $source): Collection
    {
        return $order->fulfilments
            ->filter(fn (Fulfilment $target) => $target->id !== $source->id
                && $target->location_id === $source->location_id
                && $target->method === $source->method
                && MergeFulfilments::isMergeable($target))
            ->values();
    }

    /**
     * A per-method label with a generic fallback — e.g. `handed_over_shipping`
     * ("Shipped") falling back to `handed_over_default` ("Fulfilled").
     */
    protected function methodLabel(string $prefix, string $method, string $fallback): string
    {
        $key = 'panel::orders.'.$prefix.'_'.$method;

        return Lang::has($key) ? __($key) : __('panel::orders.'.$fallback);
    }

    /**
     * How the transaction ledger compares to what the order should have
     * settled to: `outstanding` when the customer has paid something but not
     * everything, `refund_due` when settled money exceeds that reference,
     * `balanced` otherwise. A zero-capture order (pending/authorized) stays
     * balanced — that's the ordinary pre-payment state, not a divergence to
     * flag. A cancelled order's reference is 0, not its total — nothing
     * should be kept, so any money still held is a refund due regardless of
     * how much of the original total it represents.
     *
     * @return array{status: string, captured: ?string, refunded: ?string, total: string, variance: ?string, varianceMajor: float}
     */
    protected function settlement(Order $order, int $captured, int $refunded, callable $money, callable $toMajor): array
    {
        $total = (int) $order->total;
        $settled = $captured - $refunded;
        $reference = $order->isCancelled() ? 0 : $total;

        $status = match (true) {
            $settled > $reference => 'refund_due',
            $captured > 0 && $settled < $reference => 'outstanding',
            default => 'balanced',
        };

        $varianceMinor = $status === 'balanced' ? 0 : abs($settled - $reference);

        return [
            'status' => $status,
            'captured' => $captured ? $money($captured) : null,
            'refunded' => $refunded ? $money($refunded) : null,
            'total' => $money($total),
            'variance' => $varianceMinor ? $money($varianceMinor) : null,
            'varianceMajor' => $toMajor($varianceMinor),
        ];
    }

    /**
     * The delivery method chosen at checkout — the shipping-breakdown snapshot,
     * falling back to the shipping line for orders without a breakdown.
     *
     * @return array{name: string, identifier: ?string, price: ?string}|null
     */
    protected function shippingOption(Order $order, callable $money): ?array
    {
        $item = $order->shipping_breakdown?->items?->first();

        if ($item) {
            return [
                'name' => $item->name,
                'identifier' => $item->identifier,
                'price' => $item->price->format(),
            ];
        }

        $line = $order->lines->firstWhere('type', 'shipping');

        if (! $line) {
            return null;
        }

        return [
            'name' => $line->description,
            'identifier' => $order->shippingAddress?->shipping_option,
            'price' => $money($line->total),
        ];
    }

    /**
     * Flatten an order address for display and editing.
     *
     * @return array<string, mixed>|null
     */
    protected function address(?OrderAddress $address, Order $order): ?array
    {
        if (! $address) {
            return null;
        }

        return [
            'id' => $address->id,
            'type' => $address->type,
            'title' => $address->title,
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
            'country_id' => $address->country_id,
            'tax_identifier' => $address->tax_identifier,
            'contact_email' => $address->contact_email,
            'contact_phone' => $address->contact_phone,
            'delivery_instructions' => $address->delivery_instructions,
            'shipping_option' => $address->shipping_option,
            'update_url' => route('panel.orders.addresses.update', [$order, $address]),
        ];
    }
}
