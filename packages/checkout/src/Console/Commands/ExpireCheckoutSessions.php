<?php

namespace Lunar\Checkout\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\States\CheckoutSession\Expired;

/**
 * Transitions stale Open sessions to Expired (spec 0004 §C). A
 * PaymentProcessing session is deliberately left alone — payment may have
 * succeeded; the gateway reconciliation backstop resolves it.
 */
class ExpireCheckoutSessions extends Command
{
    protected $signature = 'lunar:checkout:expire-sessions';

    protected $description = 'Expire checkout sessions that have passed their expiry window.';

    public function handle(): int
    {
        $count = 0;

        CheckoutSession::query()
            ->stale()
            ->each(function (CheckoutSession $session) use (&$count): void {
                $session->status->transitionTo(Expired::class);
                $count++;
            });

        $this->info("Expired {$count} checkout session(s).");

        return self::SUCCESS;
    }
}
