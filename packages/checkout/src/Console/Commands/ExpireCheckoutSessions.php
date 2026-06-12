<?php

namespace Lunar\Checkout\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Checkout\Contracts\Actions\InvalidatesCheckoutSession;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\States\CheckoutSession\Expired;
use Lunar\Checkout\States\CheckoutSession\Open;

/**
 * Transitions expirable Open sessions to Expired via the guarded transition
 * (spec 0004 §C) — a session that enters PaymentProcessing mid-sweep loses
 * zero rows and is left alone. A session carrying an advisory intent goes
 * through void-first invalidation instead (spec 0010 §F): an unconfirmable
 * void keeps it for reconciliation.
 */
class ExpireCheckoutSessions extends Command
{
    protected $signature = 'lunar:checkout:expire-sessions';

    protected $description = 'Expire checkout sessions that have passed their expiry window.';

    public function handle(InvalidatesCheckoutSession $invalidateCheckoutSession): int
    {
        $count = 0;

        CheckoutSession::query()
            ->expirable()
            ->each(function (CheckoutSession $session) use ($invalidateCheckoutSession, &$count): void {
                // Void-first when an advisory intent exists; otherwise a plain
                // guarded transition.
                if ($session->payment_intent_ref !== null) {
                    if ($invalidateCheckoutSession->execute($session, 'expired')) {
                        $count++;
                    }

                    return;
                }

                $transitioned = $session->transitionGuarded([Open::$name], Expired::$name, [
                    'active_cart_reference' => null,
                ]);

                if ($transitioned) {
                    $count++;
                }
            });

        $this->info("Expired {$count} checkout session(s).");

        return self::SUCCESS;
    }
}
