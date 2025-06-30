<?php

namespace Lunar\Base;

use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Models\Contracts\Cart;
use Lunar\Models\Contracts\Order;
use Lunar\Models\Contracts\Transaction;

interface PaymentTypeInterface
{
    /**
     * Set the cart.
     *
     * @param  \Lunar\Models\Cart  $order
     */
    public function cart(Cart $cart): self;

    /**
     * Set the order.
     */
    public function order(Order $order): self;

    /**
     * Set any data the provider might need.
     */
    public function withData(array $data): self;

    /**
     * Set any configuration on the driver.
     */
    public function setConfig(array $config): self;

    /**
     * Authorize the payment.
     */
    public function authorize(): ?PaymentAuthorize;

    /**
     * Refund a transaction for a given amount.
     *
     * @param  null|string  $notes
     */
    public function refund(Transaction $transaction, int $amount, $notes = null): PaymentRefund;

    /**
     * Capture an amount for a transaction.
     *
     * @param  int  $amount
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture;
}
