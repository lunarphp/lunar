<?php

namespace Lunar\Core\PaymentTypes;

use Lunar\Core\Base\DataTransferObjects\PaymentChecks;
use Lunar\Core\Base\PaymentTypeInterface;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Contracts\Cart as CartContract;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Contracts\Transaction as TransactionContract;
use Lunar\Core\Models\Order;

abstract class AbstractPayment implements PaymentTypeInterface
{
    /**
     * Whether we should allow partial payments
     */
    protected bool $allowPartialPayment = false;

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

    public function allowPartialPayment(bool $condition = true): self
    {
        $this->allowPartialPayment = $condition;

        return $this;
    }

    public function getPaymentChecks(TransactionContract $transaction): PaymentChecks
    {
        return new PaymentChecks;
    }
}
