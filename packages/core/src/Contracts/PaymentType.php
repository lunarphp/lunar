<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\DataObjects\PaymentAuthorize;
use Lunar\Core\DataObjects\PaymentCapture;
use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Transaction;

interface PaymentType
{
    /**
     * Set the cart.
     *
     * @param  Cart  $order
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
     * Allow partial payments (e.g. deposits).
     */
    public function allowPartialPayment(bool $condition = true): self;

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
