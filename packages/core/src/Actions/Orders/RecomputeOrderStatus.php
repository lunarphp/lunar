<?php

namespace Lunar\Core\Actions\Orders;

use Illuminate\Support\Facades\Log;
use Lunar\Core\Contracts\Actions\Orders\RecomputesOrderStatus;
use Lunar\Core\Contracts\Actions\Orders\ResolvesFulfilmentStatus;
use Lunar\Core\Contracts\Actions\Orders\ResolvesPaymentStatus;
use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\Events\Orders\OrderFulfilmentStatusUpdated;
use Lunar\Core\Events\Orders\OrderPaymentStatusUpdated;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

/**
 * Recompute the derived `payment_status` / `fulfilment_status` columns and
 * re-derive the headline `status`.
 *
 * The two rollups are written with `saveQuietly()` so they never loop back
 * through the order observer; the headline change instead flows through a
 * guarded `transitionTo()` so `OrderStatusUpdated` still fires and an illegal
 * derived jump is logged rather than silently applied. While the order sits
 * in a manual override state, headline derivation is suppressed entirely.
 */
final class RecomputeOrderStatus implements RecomputesOrderStatus
{
    public function __construct(
        protected ResolvesPaymentStatus $resolvePaymentStatus,
        protected ResolvesFulfilmentStatus $resolveFulfilmentStatus,
        protected OrderStateConfig $config,
    ) {}

    public function execute(OrderContract $order): Order
    {
        /** @var Order $order */
        $previousPayment = $order->payment_status;
        $previousFulfilment = $order->fulfilment_status;

        $paymentClass = $this->resolvePaymentStatus->execute($order);
        $fulfilmentClass = $this->resolveFulfilmentStatus->execute($order);

        $order->payment_status = new $paymentClass($order);
        $order->fulfilment_status = new $fulfilmentClass($order);

        // Quiet write so this never loops back through the order observer; the
        // headline change below instead flows through a guarded transition.
        $order->saveQuietly();

        if ($previousPayment::class !== $paymentClass) {
            OrderPaymentStatusUpdated::dispatch($order, $previousPayment, $order->payment_status);
        }

        if ($previousFulfilment::class !== $fulfilmentClass) {
            OrderFulfilmentStatusUpdated::dispatch($order, $previousFulfilment, $order->fulfilment_status);
        }

        $this->deriveHeadline($order);

        return $order;
    }

    /**
     * Derive and apply the headline status, unless suppressed by an override.
     */
    protected function deriveHeadline(Order $order): void
    {
        if (in_array($order->status::class, $this->config->overrideStates(), true)) {
            return;
        }

        $targetClass = $this->config->computeOrderStatus(
            $order->payment_status,
            $order->fulfilment_status,
        );

        if ($targetClass === $order->status::class) {
            return;
        }

        try {
            $order->status->transitionTo($targetClass);
        } catch (CouldNotPerformTransition $e) {
            Log::warning('Derived order status is not a legal transition; leaving status unchanged.', [
                'order_id' => $order->id,
                'from' => $order->status::class,
                'to' => $targetClass,
            ]);
        }
    }
}
