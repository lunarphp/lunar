<?php

namespace Lunar\Core\PaymentTypes;

use Lunar\Core\Contracts\PaymentType;
use Lunar\Core\DataObjects\PaymentChecks;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Transaction;

abstract class AbstractPayment implements PaymentType
{
    /**
     * Whether we should allow partial payments
     */
    protected bool $allowPartialPayment = false;

    /**
     * The instance of the cart.
     */
    protected ?Cart $cart = null;

    /**
     * The instance of the order.
     */
    protected ?Order $order = null;

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
    public function cart(Cart $cart): self
    {
        /** @var Cart $cart */
        $this->cart = $cart;
        $this->order = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function order(Order $order): self
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

    public function allowPartialPayment(bool $condition = true): self
    {
        $this->allowPartialPayment = $condition;

        return $this;
    }

    public function getPaymentChecks(Transaction $transaction): PaymentChecks
    {
        return new PaymentChecks;
    }
}
