<?php

namespace Lunar\Checkout\Actions;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Lunar\Checkout\Contracts\Actions\CreatesCheckoutSession;
use Lunar\Checkout\Contracts\Actions\InvalidatesCheckoutSession;
use Lunar\Checkout\DataObjects\CartSnapshot;
use Lunar\Checkout\Events\CheckoutSessionCreated;
use Lunar\Checkout\Events\CheckoutSessionSuperseded;
use Lunar\Checkout\Exceptions\CheckoutSessionConflictException;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\States\CheckoutSession\Open;
use Lunar\Checkout\States\CheckoutSession\PaymentProcessing;
use Lunar\Core\Contracts\StorefrontSession;
use Lunar\Core\Models\Cart;

/**
 * The default `LunarCheckoutDriver` ingest path (spec 0004 §B). Pins
 * channel/currency/locale and the driver-computed fingerprint, and enforces
 * one active session per cart (spec 0010 §F.2): a prior `Open` sibling is
 * superseded (void-first), a `PaymentProcessing` sibling refuses the create.
 * The `active_cart_reference` unique index backstops the create/create race.
 */
final class CreateCheckoutSession implements CreatesCheckoutSession
{
    public function __construct(
        private StorefrontSession $storefrontSession,
        private InvalidatesCheckoutSession $invalidateCheckoutSession,
        private ConnectionInterface $db,
        private Dispatcher $events,
    ) {}

    public function execute(Cart $cart, CartSnapshot $snapshot, array $attributes = []): CheckoutSession
    {
        try {
            return $this->attempt($cart, $snapshot, $attributes);
        } catch (UniqueConstraintViolationException) {
            // Lost a concurrent create/create race: the winner's row now
            // exists, so a single retry either supersedes it or yields the
            // sibling-conflict 409 (0010 §F.2).
            return $this->attempt($cart, $snapshot, $attributes);
        }
    }

    private function attempt(Cart $cart, CartSnapshot $snapshot, array $attributes): CheckoutSession
    {
        return $this->db->transaction(function () use ($cart, $snapshot, $attributes): CheckoutSession {
            $cartReference = (string) $cart->id;

            $siblings = CheckoutSession::query()
                ->where('cart_reference', $cartReference)
                ->whereIn('status', [Open::$name, PaymentProcessing::$name])
                ->get();

            $superseded = [];

            foreach ($siblings as $sibling) {
                if ($sibling->status instanceof PaymentProcessing) {
                    throw new CheckoutSessionConflictException(
                        ($sibling->meta['reconciliation_stalled'] ?? false)
                            ? 'stalled'
                            : 'sibling_payment_processing'
                    );
                }

                // Void-first supersede; an unconfirmable void blocks it.
                if (! $this->invalidateCheckoutSession->execute($sibling, 'superseded')) {
                    throw new CheckoutSessionConflictException('sibling_payment_processing');
                }

                $superseded[] = $sibling;
            }

            $session = CheckoutSession::create([
                'cart_reference' => $cartReference,
                'channel_handle' => $snapshot->channelHandle,
                'currency_code' => $snapshot->currencyCode,
                'locale' => $attributes['locale'] ?? app()->getLocale(),
                'cart_fingerprint' => $snapshot->fingerprint,
                'amount_subtotal' => $snapshot->amountSubtotal,
                'amount_total' => $snapshot->amountTotal,
                'status' => Open::$name,
                'customer_reference' => $this->resolveCustomerReference(),
                'customer_email' => $attributes['customer_email'] ?? null,
                'client_reference_id' => $attributes['client_reference_id'] ?? null,
                'success_url' => $attributes['success_url'] ?? null,
                'cancel_url' => $attributes['cancel_url'] ?? null,
                'metadata' => $attributes['metadata'] ?? null,
                'expires_at' => now()->addHours(
                    (int) config('lunar.checkout.session.expires_after', 24)
                ),
            ]);

            foreach ($superseded as $sibling) {
                $this->events->dispatch(new CheckoutSessionSuperseded($sibling, $session));
            }

            $this->events->dispatch(new CheckoutSessionCreated($session));

            return $session;
        });
    }

    private function resolveCustomerReference(): ?string
    {
        $id = $this->storefrontSession->getCustomer()?->id;

        return $id !== null ? (string) $id : null;
    }
}
