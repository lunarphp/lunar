<?php

namespace Lunar\Stripe\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;
use Lunar\Models\Order;

class ProcessStripeWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public string $paymentIntentId,
        public ?string $orderId
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Do we have an order with this intent?
        $cart = null;
        $order = null;

        if ($this->orderId) {
            $order = Order::find($this->orderId);

            if ($order->placed_at) {
                return;
            }
        }

        if (! $order) {
            $cart = Cart::where('meta->payment_intent', '=', $this->paymentIntentId)->first();
        }

        if (! $cart) {
            Log::error(
                "Unable to find cart with intent {$this->paymentIntentId}"
            );

            return;
        }

        $payment = Payments::driver('stripe')->cart($cart->calculate())->withData([
            'payment_intent' => $this->paymentIntentId,
        ]);

        if ($order) {
            $payment = $payment->order($order)->authorize();
            PaymentAttemptEvent::dispatch($payment);

            return;
        }

        $payment = $payment->cart($cart->calculate())->authorize();
        PaymentAttemptEvent::dispatch($payment);
    }
}
