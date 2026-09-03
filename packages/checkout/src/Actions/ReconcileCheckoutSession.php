<?php

namespace Lunar\Checkout\Actions;

use Illuminate\Contracts\Events\Dispatcher;
use Lunar\Checkout\Contracts\Actions\ReconcilesCheckoutSession;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Events\CheckoutCompletionFailed;
use Lunar\Checkout\Events\CheckoutSessionInvalidated;
use Lunar\Checkout\Events\CheckoutSessionReconciliationStalled;
use Lunar\Checkout\Exceptions\PaymentConfirmationException;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\States\CheckoutSession\Cancelled;
use Lunar\Checkout\States\CheckoutSession\Open;
use Lunar\Checkout\States\CheckoutSession\PaymentProcessing;
use Lunar\Checkout\Support\PaymentIntentGateway;
use Lunar\Core\Contracts\SupportsPaymentHolds;
use Lunar\Core\Contracts\SupportsPaymentIntents;
use Lunar\Core\Enums\PaymentIntentStatus;

/**
 * Bounded resolution of a `PaymentProcessing` session (spec 0010 §F): the
 * gateway's actual intent outcome decides — captured completes (or refunds on
 * mismatch: no charge survives without an order), voided/failed reopens,
 * pending reschedules without consuming an attempt. Exhausted attempts fire
 * {@see CheckoutSessionReconciliationStalled} once; the operator `--resolve`
 * override is the sanctioned stall exit.
 */
final class ReconcileCheckoutSession implements ReconcilesCheckoutSession
{
    public function __construct(
        private CheckoutDriver $driver,
        private PaymentIntentGateway $gateways,
        private Dispatcher $events,
    ) {}

    public function execute(CheckoutSession $session, ?string $resolve = null): string
    {
        if (! $session->status instanceof PaymentProcessing) {
            return 'not-applicable';
        }

        $gateway = $this->gateways->for($session);
        $reference = $session->payment_intent_ref;

        if ($resolve !== null) {
            return $this->resolveByOperator($session, $gateway, $reference, $resolve);
        }

        if ($gateway === null || $reference === null) {
            return $this->recordAttempt($session);
        }

        try {
            $status = $gateway->fetchIntent($reference);
        } catch (\Throwable) {
            return $this->recordAttempt($session);
        }

        return match ($status) {
            // Legitimately still in flight (slow methods) — reschedule
            // without consuming an attempt (spec 0010 §F).
            PaymentIntentStatus::Pending => 'pending',

            PaymentIntentStatus::Captured,
            PaymentIntentStatus::RequiresCapture => $this->completeOrRefund($session, $gateway, $reference),

            PaymentIntentStatus::Voided,
            PaymentIntentStatus::Failed => $this->reopen($session, 'intent_'.$status->value),
        };
    }

    public function release(CheckoutSession $session): string
    {
        if (! $session->status instanceof PaymentProcessing) {
            return 'not-applicable';
        }

        $gateway = $this->gateways->for($session);
        $reference = $session->payment_intent_ref;

        if ($reference !== null) {
            // An intent exists, so money may exist. Reopen only on the
            // gateway's word that it was not captured; anything unknowable
            // stays frozen and the reconciliation sweep owns it.
            if ($gateway === null) {
                return 'unconfirmed';
            }

            try {
                $status = $gateway->fetchIntent($reference);
            } catch (\Throwable) {
                return 'unconfirmed';
            }

            if (in_array($status, [PaymentIntentStatus::Captured, PaymentIntentStatus::RequiresCapture], true)) {
                // An authorise-only hold at RequiresCapture is not money
                // moved: the customer may keep editing and re-confirm, so
                // reopen with the hold intact instead of forcing completion.
                if (! ($session->isHoldMode() && $status === PaymentIntentStatus::RequiresCapture)) {
                    return $this->completeOrRefund($session, $gateway, $reference);
                }
            }
        }

        // Unlike reopen(), the intent reference survives: the customer's
        // mounted payment element is still bound to it, the intent is still
        // confirmable, and the retry re-syncs its amount at the pay boundary.
        $grace = (int) config('lunar.checkout.session.reopen_grace_minutes', 30);

        $session->transitionGuarded([PaymentProcessing::$name], Open::$name, [
            'payment_processing_at' => null,
            'reconciliation_attempts' => 0,
            'expires_at' => now()->addMinutes($grace),
        ]);

        return 'released';
    }

    /**
     * Captured money resolves to exactly one of: an order, or money back.
     * Money back is flavour-aware: an authorise-only hold that was never
     * captured voids; anything actually captured (a standard intent, or a
     * hold captured by this very call) refunds.
     */
    private function completeOrRefund(
        CheckoutSession $session,
        SupportsPaymentIntents $gateway,
        string $reference,
    ): string {
        // An authorise-only hold is not money moved yet: capture it now, in
        // the one idempotent place every completion path funnels through, so
        // a crash between pin and capture recovers on the next reconcile.
        // Policy-mode requires_capture (store-wide manual capture, settled
        // later from the panel) keeps its existing complete-without-capture
        // behaviour.
        $holdCaptured = false;

        if ($session->isHoldMode() && $gateway instanceof SupportsPaymentHolds) {
            try {
                $gateway->captureHold($reference, (int) $session->amount_total);
                $holdCaptured = true;
            } catch (\Throwable $e) {
                report($e);

                // Nothing was captured: the intent reference survives so the
                // customer can retry against the SAME hold, or the wallet
                // re-opens against it, instead of minting a fresh
                // authorisation.
                $this->reopenKeepingIntent($session, 'hold_capture_failed');
                $this->events->dispatch(new CheckoutCompletionFailed($session, 'hold-capture-failed'));

                return 'reopened';
            }
        }

        try {
            $this->driver->complete($session);

            return 'completed';
        } catch (PaymentConfirmationException) {
            // The cart no longer matches what the customer paid for.
        }

        // Money back: captured funds refund; an uncaptured hold voids. A
        // hold-mode session that reaches here with $holdCaptured still false
        // means $gateway lacked SupportsPaymentHolds (a misconfiguration);
        // voiding is still the safe answer.
        if ($session->isHoldMode() && ! $holdCaptured) {
            try {
                $gateway->voidIntent($reference);
            } catch (\Throwable) {
                $this->events->dispatch(new CheckoutCompletionFailed($session, 'void-failed'));

                return $this->recordAttempt($session);
            }

            $this->reopen($session, 'hold_voided');
            $this->events->dispatch(new CheckoutCompletionFailed($session, 'hold-voided'));

            return 'voided';
        }

        try {
            $refundReference = $gateway->refundIntent(
                $reference,
                $session->amount_total,
                'lunar-checkout-refund:'.$reference,
            );
        } catch (\Throwable) {
            $this->events->dispatch(new CheckoutCompletionFailed($session, 'refund-failed'));

            return $this->recordAttempt($session);
        }

        $this->reopen($session, 'refunded');
        $this->events->dispatch(new CheckoutCompletionFailed($session, 'refunded', $refundReference));

        return 'refunded';
    }

    private function resolveByOperator(
        CheckoutSession $session,
        ?SupportsPaymentIntents $gateway,
        ?string $reference,
        string $resolve,
    ): string {
        return match ($resolve) {
            'complete' => $this->completeForOperator($session),
            'refund' => $gateway !== null && $reference !== null
                ? $this->completeOrRefundByForce($session, $gateway, $reference)
                : 'refund-failed',
            'cancel' => $this->cancelForOperator($session),
            default => throw new \InvalidArgumentException("Unknown resolution [{$resolve}]."),
        };
    }

    private function completeForOperator(CheckoutSession $session): string
    {
        $this->driver->complete($session);

        return 'completed';
    }

    /**
     * Operator asserts the charge must not stand: refund without trying to
     * complete first.
     */
    private function completeOrRefundByForce(
        CheckoutSession $session,
        SupportsPaymentIntents $gateway,
        string $reference,
    ): string {
        $refundReference = $gateway->refundIntent(
            $reference,
            $session->amount_total,
            'lunar-checkout-refund:'.$reference,
        );

        $this->reopen($session, 'refunded');
        $this->events->dispatch(new CheckoutCompletionFailed($session, 'refunded', $refundReference));

        return 'refunded';
    }

    /**
     * Operator asserts the gateway outcome is void/no-charge: terminalize.
     */
    private function cancelForOperator(CheckoutSession $session): string
    {
        $session->transitionGuarded([PaymentProcessing::$name], Cancelled::$name, [
            'active_cart_reference' => null,
            'cancelled_at' => now(),
        ]);

        $this->events->dispatch(new CheckoutSessionInvalidated($session, 'operator_cancelled'));

        return 'cancelled';
    }

    /**
     * Back to `Open` with return-path hygiene (spec 0010 §E.2/§F): intent refs
     * cleared in the same guarded statement, expiry re-armed with a grace
     * window so the session doesn't reopen pre-expired.
     */
    private function reopen(CheckoutSession $session, string $reason): string
    {
        $grace = (int) config('lunar.checkout.session.reopen_grace_minutes', 30);

        $session->transitionGuarded([PaymentProcessing::$name], Open::$name, [
            'payment_intent_ref' => null,
            'payment_processing_at' => null,
            'reconciliation_attempts' => 0,
            'expires_at' => now()->addMinutes($grace),
        ]);

        return $reason;
    }

    /**
     * Back to `Open` like {@see reopen()} but the intent reference survives:
     * nothing was captured (the hold's own capture call failed), so there is
     * no double-hold risk in leaving the customer bound to the same
     * authorisation instead of forcing a fresh one.
     */
    private function reopenKeepingIntent(CheckoutSession $session, string $reason): string
    {
        $grace = (int) config('lunar.checkout.session.reopen_grace_minutes', 30);

        $session->transitionGuarded([PaymentProcessing::$name], Open::$name, [
            'payment_processing_at' => null,
            'reconciliation_attempts' => 0,
            'expires_at' => now()->addMinutes($grace),
        ]);

        return $reason;
    }

    private function recordAttempt(CheckoutSession $session): string
    {
        $maxAttempts = (int) config('lunar.checkout.reconciliation.max_attempts', 5);

        $session->increment('reconciliation_attempts');
        $session->refresh();

        if ($session->reconciliation_attempts < $maxAttempts) {
            return 'retry';
        }

        // Fire the stall event ONCE; the meta flag drops the session to the
        // low-frequency tier and feeds the post-stall 409 reason (0010 §F).
        if (! ($session->meta['reconciliation_stalled'] ?? false)) {
            $session->meta = array_merge(
                (array) ($session->meta?->getArrayCopy() ?? []),
                ['reconciliation_stalled' => true],
            );
            $session->save();

            $this->events->dispatch(new CheckoutSessionReconciliationStalled($session));
        }

        return 'stalled';
    }
}
