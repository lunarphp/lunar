<?php

namespace Lunar\Base;

use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Models\Cart;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

interface PaymentTypeInterface
{
    /**
     * Set the cart.
     *
     * @param  \Lunar\Models\Cart  $order
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
     * @param  \Lunar\Models\Transaction  $transaction
     * @param  int  $amount
     * @param  null|string  $notes
     * @return \Lunar\Base\DataTransferObjects\PaymentRefund
     */
    public function refund(Transaction $transaction, int $amount, $notes = null): PaymentRefund;

    /**
     * Capture an amount for a transaction.
     *
     * @param  \Lunar\Models\Transaction  $transaction
     * @param  int  $amount
     * @return \Lunar\Base\DataTransferObjects\PaymentCapture
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture;
}
