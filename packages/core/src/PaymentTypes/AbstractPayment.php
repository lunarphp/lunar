<?php

namespace GetCandy\PaymentTypes;

use GetCandy\Base\PaymentTypeInterface;
use GetCandy\Models\Cart;
use GetCandy\Models\Order;

abstract class AbstractPayment implements PaymentTypeInterface
{
    /**
     * The instance of the cart.
     *
     * @var \GetCandy\Models\Cart
     */
    protected ?Cart $cart = null;

    /**
     * The instance of the order.
     *
     * @var \GetCandy\Models\Order
     */
    protected ?Order $order = null;

    /**
     * Any config for this payment provider.
     *
     * @var array
     */
    protected array $config = [];

    /**
     * {@inheritDoc}
     */
    public function cart(Cart $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function order(Order $order): self
    {
        $this->order = $order;

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
}
