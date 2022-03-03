<?php

namespace GetCandy\Base;

use GetCandy\Models\Cart;
use GetCandy\Models\Order;

interface PaymentTypeInterface
{
    /**
     * Set the cart.
     *
     * @param \GetCandy\Models\Cart $order
     * @return self
     */
    public function cart(Cart $cart): self;

    /**
     * Set the order.
     *
     * @param Order $order
     * @return self
     */
    public function order(Order $order): self;

    /**
     * Set any data the provider might need.
     *
     * @param array $data
     * @return self
     */
    public function withData(array $data): self;

    /**
     * Set any configuration on the driver.
     *
     * @param array $config
     * @return self
     */
    public function setConfig(array $config): self;

    /**
     * Release the payment.
     *
     * @return void
     */
    public function release();

    /**
     * Refund an amount against the order.
     *
     * @param integer $amount
     * @return void
     */
    public function refund(int $amount);
}
