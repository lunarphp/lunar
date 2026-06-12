<?php

namespace Lunar\Checkout\Actions;

use Illuminate\Contracts\Events\Dispatcher;
use Lunar\Checkout\Contracts\Actions\InvalidatesCheckoutSession;
use Lunar\Checkout\Contracts\Actions\SyncsCheckoutSession;
use Lunar\Checkout\DataObjects\CartSnapshot;
use Lunar\Checkout\Events\CheckoutSessionResynced;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\States\CheckoutSession\Open;

/**
 * Guarded single-statement re-sync while `Open` (spec 0010 §D). The predicate
 * is the freeze: zero affected rows means the session left `Open` mid-flight
 * (a pay pin won the race) and the sync is dropped — never clobbering the
 * pinned contract. Currency/channel divergence is not absorbed: it invalidates.
 */
final class SyncCheckoutSession implements SyncsCheckoutSession
{
    public function __construct(
        private InvalidatesCheckoutSession $invalidateCheckoutSession,
        private Dispatcher $events,
    ) {}

    public function execute(CheckoutSession $session, CartSnapshot $snapshot): bool
    {
        if ($snapshot->currencyCode !== $session->currency_code
            || $snapshot->channelHandle !== $session->channel_handle) {
            $this->invalidateCheckoutSession->execute($session, 'context_diverged');

            return false;
        }

        $updated = CheckoutSession::query()
            ->whereKey($session->getKey())
            ->where('status', Open::$name)
            ->update([
                'amount_subtotal' => $snapshot->amountSubtotal,
                'amount_total' => $snapshot->amountTotal,
                'cart_fingerprint' => $snapshot->fingerprint,
                'updated_at' => now(),
            ]);

        $session->refresh();

        if ($updated === 1) {
            $this->events->dispatch(new CheckoutSessionResynced($session, $snapshot));

            return true;
        }

        return false;
    }
}
