<?php

namespace GetCandy\Base;

use GetCandy\Base\DataTransferObjects\PaymentAuthorize;
use GetCandy\Base\DataTransferObjects\PaymentCapture;
use GetCandy\Base\DataTransferObjects\PaymentRefund;
use GetCandy\Models\Cart;
use GetCandy\Models\Order;
use GetCandy\Models\Transaction;

interface PaymentTypeInterface
{
    /**
     * Set the cart.
     *
     * @param  \GetCandy\Models\Cart  $order
     * @return self
     */
    public function cart(Cart $cart): self;

    /**
     * Set the order.
     *
     * @param  Order  $order
     * @return self
     */
    public function order(Order $order): self;

    /**
     * Set any data the provider might need.
     *
     * @param  array  $data
     * @return self
     */
    public function withData(array $data): self;

    /**
     * Set any configuration on the driver.
     *
     * @param  array  $config
     * @return self
     */
    public function setConfig(array $config): self;

    /**
     * Authorize the payment.
     *
     * @return void
     */
    public function authorize(): PaymentAuthorize;

    /**
     * Refund a transaction for a given amount.
     *
     * @param  \GetCandy\Models\Transaction  $transaction
     * @param  int  $amount
     * @param  null|string  $notes
     * @return \GetCandy\Base\DataTransferObjects\PaymentRefund
     */
    public function refund(Transaction $transaction, int $amount, $notes = null): PaymentRefund;

    /**
     * Capture an amount for a transaction.
     *
     * @param  \GetCandy\Models\Transaction  $transaction
     * @param  int  $amount
     * @return \GetCandy\Base\DataTransferObjects\PaymentCapture
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture;
}
