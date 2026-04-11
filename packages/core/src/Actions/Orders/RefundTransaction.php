<?php

namespace Lunar\Actions\Orders;

use Lunar\Actions\AbstractAction;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Base\DataTransferObjects\RefundRequest;
use Lunar\Base\RefundAuthorizationInterface;
use Lunar\Events\RefundCompleted;
use Lunar\Events\RefundFailed;
use Lunar\Events\RefundRequested;
use Lunar\Models\Transaction;
use Throwable;

class RefundTransaction extends AbstractAction
{
    public function __construct(
        protected RefundAuthorizationInterface $refundAuthorization,
    ) {
        //
    }

    public function execute(RefundRequest $refundRequest): PaymentRefund
    {
        $authorization = $this->refundAuthorization->authorize($refundRequest);

        if (! $authorization->authorized) {
            $paymentRefund = new PaymentRefund(
                success: false,
                message: $authorization->message,
                lineAllocations: $refundRequest->lineAllocations,
            );

            RefundFailed::dispatch(
                refundRequest: $refundRequest,
                paymentRefund: $paymentRefund,
                message: $authorization->message,
                meta: $authorization->meta,
            );

            return $paymentRefund;
        }

        RefundRequested::dispatch($refundRequest);

        $existingRefundIds = $refundRequest->transaction->order
            ->refunds()
            ->pluck('id');

        try {
            $paymentRefund = $refundRequest->transaction->refund(
                $refundRequest->amount,
                $refundRequest->notes,
            );
        } catch (Throwable $e) {
            $paymentRefund = new PaymentRefund(
                success: false,
                message: $e->getMessage(),
                lineAllocations: $refundRequest->lineAllocations,
            );

            RefundFailed::dispatch(
                refundRequest: $refundRequest,
                paymentRefund: $paymentRefund,
                message: $e->getMessage(),
                meta: $authorization->meta,
            );

            return $paymentRefund;
        }

        $paymentRefund->lineAllocations ??= $refundRequest->lineAllocations;

        if (! $paymentRefund->refundTransactionId) {
            $refundTransaction = $refundRequest->transaction->order
                ->refunds()
                ->whereNotIn('id', $existingRefundIds)
                ->latest('id')
                ->first();

            if ($refundTransaction) {
                $this->hydratePaymentRefund($paymentRefund, $refundTransaction);
            }
        }

        if (! $paymentRefund->success) {
            RefundFailed::dispatch(
                refundRequest: $refundRequest,
                paymentRefund: $paymentRefund,
                message: $paymentRefund->message,
                meta: $authorization->meta,
            );

            return $paymentRefund;
        }

        RefundCompleted::dispatch($refundRequest, $paymentRefund);

        return $paymentRefund;
    }

    protected function hydratePaymentRefund(PaymentRefund $paymentRefund, Transaction $refundTransaction): void
    {
        $paymentRefund->refundTransactionId ??= $refundTransaction->id;
        $paymentRefund->reference ??= $refundTransaction->reference;
        $paymentRefund->status ??= $refundTransaction->status;
        $paymentRefund->meta ??= $refundTransaction->meta?->getArrayCopy() ?: null;
    }
}
