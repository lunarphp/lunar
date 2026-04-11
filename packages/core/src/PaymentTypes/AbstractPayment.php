<?php

namespace Lunar\PaymentTypes;

use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentChecks;
use Lunar\Base\PaymentTypeInterface;
use Lunar\Events\OrderPaid;
use Lunar\Models\Cart;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Models\Contracts\Order as OrderContract;
use Lunar\Models\Contracts\Transaction as TransactionContract;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

abstract class AbstractPayment implements PaymentTypeInterface
{
    /**
     * The instance of the cart.
     */
    protected ?CartContract $cart = null;

    /**
     * The instance of the order.
     */
    protected ?OrderContract $order = null;

    /**
     * Any config for this payment provider.
     */
    protected array $config = [];

    /**
     * Data storage.
     */
    protected array $data = [];

    /**
     * {@inheritDoc}
     */
    public function cart(CartContract $cart): self
    {
        /** @var Cart $cart */
        $this->cart = $cart;
        $this->order = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function order(OrderContract $order): self
    {
        /** @var Order $order */
        $this->order = $order;
        $this->cart = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getPaymentChecks(TransactionContract $transaction): PaymentChecks
    {
        return new PaymentChecks;
    }

    /**
     * Dispatch the canonical post-purchase event when an order becomes paid.
     *
     * @param  array<string, mixed>  $meta
     */
    protected function dispatchOrderPaidEvent(PaymentAuthorize $paymentAuthorize, array $meta = []): void
    {
        if (! $paymentAuthorize->success || ! $this->order?->isPlaced()) {
            return;
        }

        /** @var Transaction|null $transaction */
        $transaction = $this->order->transactions()
            ->whereSuccess(true)
            ->whereIn('type', ['capture', 'intent'])
            ->latest('id')
            ->first();

        OrderPaid::dispatch(
            order: $this->order,
            paymentAuthorize: $paymentAuthorize,
            transaction: $transaction,
            meta: $meta,
        );
    }
}
