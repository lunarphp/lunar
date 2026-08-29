<?php

namespace Lunar\Paypal\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Order;
use Lunar\Paypal\Events\PaypalWebhookReceived;
use Lunar\Paypal\Managers\PaypalManager;
use Lunar\Paypal\Models\PaypalOrder;

class ProcessPaypalWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The event types the driver acts on. Anything else is dispatched as a
     * PaypalWebhookReceived event and otherwise ignored.
     */
    const HANDLED_EVENTS = [
        'CHECKOUT.ORDER.APPROVED',
        'PAYMENT.CAPTURE.COMPLETED',
        'PAYMENT.CAPTURE.DENIED',
        'PAYMENT.CAPTURE.PENDING',
        'PAYMENT.CAPTURE.REFUNDED',
        'CUSTOMER.DISPUTE.CREATED',
    ];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public array $payload,
    ) {}

    public function handle(): void
    {
        $eventType = $this->payload['event_type'] ?? '';

        PaypalWebhookReceived::dispatch($eventType, $this->payload);

        match ($eventType) {
            'CHECKOUT.ORDER.APPROVED',
            'PAYMENT.CAPTURE.COMPLETED' => $this->placeOrder(),
            'PAYMENT.CAPTURE.REFUNDED' => $this->recordRefund(),
            default => null,
        };
    }

    /**
     * Carry an approved or captured PayPal order through to a placed Lunar
     * order, for the case where the customer never made it back to the
     * storefront to trigger authorize() themselves.
     */
    protected function placeOrder(): void
    {
        $paypalOrder = $this->paypalOrder();

        if (! $paypalOrder || $paypalOrder->isProcessed()) {
            return;
        }

        $payment = Payments::driver('paypal')->withData([
            'paypal_order_id' => $paypalOrder->paypal_order_id,
        ]);

        if ($order = $paypalOrder->order) {
            if ($order->placed_at) {
                return;
            }

            $payment->order($order)->authorize();

            return;
        }

        if ($cart = $paypalOrder->cart) {
            $payment->cart($cart->calculate())->authorize();
        }
    }

    /**
     * Record a refund raised outside Lunar — most often issued directly from the
     * PayPal dashboard, which the storefront never sees.
     */
    protected function recordRefund(): void
    {
        $resource = $this->payload['resource'] ?? [];

        if (! $refundId = ($resource['id'] ?? null)) {
            return;
        }

        $order = $this->paypalOrder()?->order ?: $this->orderFromCapture($resource);

        if (! $order) {
            return;
        }

        // The driver's own refund() already wrote this row when the refund was
        // raised from the admin.
        if ($order->transactions()->where('reference', $refundId)->exists()) {
            return;
        }

        $capture = $order->transactions()->where('type', 'capture')->first();

        $order->transactions()->create([
            'success' => ($resource['status'] ?? null) === 'COMPLETED',
            'type' => 'refund',
            'driver' => 'paypal',
            'amount' => PaypalManager::fromPaypalAmount(
                (string) ($resource['amount']['value'] ?? '0'),
                $order->currency
            ),
            'reference' => $refundId,
            'status' => $resource['status'] ?? null,
            'notes' => 'Refunded via PayPal',
            'card_type' => $capture?->card_type ?: 'paypal',
            'last_four' => $capture?->last_four,
        ]);
    }

    /**
     * Fall back to the capture reference when the PayPal order is not on record
     * — a refund can arrive for an order placed before this table existed.
     */
    protected function orderFromCapture(array $resource): ?Order
    {
        $captureId = $resource['links'][0]['href'] ?? null;

        if (! $captureId = ($resource['capture_id'] ?? ($captureId ? basename(parse_url($captureId, PHP_URL_PATH) ?: '') : null))) {
            return null;
        }

        return Order::whereHas(
            'transactions',
            fn ($query) => $query->where('reference', $captureId)->where('type', 'capture')
        )->first();
    }

    protected function paypalOrder(): ?PaypalOrder
    {
        if (! $paypalOrderId = static::resolvePaypalOrderId($this->payload)) {
            return null;
        }

        return PaypalOrder::where('paypal_order_id', $paypalOrderId)->first();
    }

    /**
     * The PayPal order ID an event relates to.
     *
     * `CHECKOUT.ORDER.*` events are keyed by it directly; `PAYMENT.CAPTURE.*`
     * events are keyed by capture ID and carry the order ID in supplementary
     * data.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function resolvePaypalOrderId(array $payload): ?string
    {
        $resource = $payload['resource'] ?? [];

        if (str_starts_with($payload['event_type'] ?? '', 'CHECKOUT.ORDER.')) {
            return $resource['id'] ?? null;
        }

        return $resource['supplementary_data']['related_ids']['order_id'] ?? null;
    }
}
