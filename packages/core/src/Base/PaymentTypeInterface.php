<?php

namespace GetCandy\Base;

use GetCandy\Base\DataTransferObjects\PaymentRefund;
use GetCandy\Base\DataTransferObjects\PaymentRelease;
use GetCandy\Models\Cart;
use GetCandy\Models\Order;
use GetCandy\Models\Transaction;

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
    public function release(): PaymentRelease;

    /**
     * Refund a transaction for a given amount
     *
     * @param \GetCandy\Models\Transaction $transaction
     * @param integer $amount
     * @param null|string $notes
     * @return \GetCandy\Base\DataTransferObjects\PaymentRefund
     */
    public function refund(Transaction $transaction, int $amount, $notes = null): PaymentRefund;
}
