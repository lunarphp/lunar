<?php

namespace Lunar\Checkout\Contracts;

use Lunar\Checkout\DataObjects\CartSnapshot;
use Lunar\Checkout\DataObjects\CheckoutAddress;
use Lunar\Checkout\Drivers\AbstractCheckoutDriver;
use Lunar\Checkout\Exceptions\CheckoutSessionConflictException;
use Lunar\Checkout\Exceptions\CheckoutSessionNotOperableException;
use Lunar\Checkout\Exceptions\PaymentConfirmationException;
use Lunar\Checkout\Managers\CheckoutSessionManager;
use Lunar\Checkout\Models\CheckoutSession;

/**
 * The swap seam (specs 0004/0010). Owns the backend-specific ends of the
 * checkout lifecycle — turning a source cart into a session, mediating every
 * cart read/write while the session is `Open`, and finalising the session into
 * an order. Everything between (the `checkout_sessions` table, the UUID
 * capability token, the state machine, the element model) is backend-neutral
 * and Lunar-owned.
 *
 * Third-party drivers MUST extend {@see AbstractCheckoutDriver}
 * (spec 0010 §B) — future verbs land there with default implementations, so
 * interface growth doesn't break drivers.
 *
 * Resolved by name from `config('lunar.checkout.driver')` via the
 * {@see CheckoutSessionManager} (standard Manager pattern). Default: `lunar`.
 */
interface CheckoutDriver
{
    /**
     * Ingest an arbitrary source cart into a checkout session — pinning
     * currency/channel/locale and the driver-computed fingerprint, and
     * enforcing one active session per cart (0010 §F.2).
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws CheckoutSessionConflictException
     */
    public function createSession(mixed $source, array $attributes = []): CheckoutSession;

    /**
     * Idempotent ingest for repeat renders (e.g. GET /checkout): return the
     * source cart's live `Open` session when one exists, otherwise create one.
     * A refresh must not churn or supersede a healthy in-progress session.
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws CheckoutSessionConflictException
     */
    public function resolveOrCreateSession(mixed $source, array $attributes = []): CheckoutSession;

    /**
     * Finalise a session into an order in whatever system the backend owns,
     * writing the driver-opaque `order_reference`. Re-verifies the live cart
     * against the pinned fingerprint inside the order transaction (0010 §E.2).
     * For the synchronous path (`Open → Completed`) the pay gate runs here too,
     * against `$confirmationToken`. MUST be idempotent keyed on the session
     * uuid: a retry returns the existing order instead of double-creating.
     *
     * @throws PaymentConfirmationException
     */
    public function complete(CheckoutSession $session, ?string $confirmationToken = null): mixed;

    /**
     * Live, never-persisted view of the source cart (0010 §B). Reads never write.
     */
    public function snapshot(CheckoutSession $session): CartSnapshot;

    /**
     * The driver-owned integrity fingerprint (0010 §D): MUST change whenever
     * the payable total, line content, address identity, shipping option,
     * coupon or currency change. Never derived from customer identity.
     */
    public function fingerprint(CheckoutSession $session): string;

    /**
     * The pay-boundary gate (0010 §E): step 0 (live currency/channel equal the
     * pinned values), confirmation-token match, Gate 2, then the atomic pin —
     * one statement transitioning `Open → PaymentProcessing` with the pinned
     * amounts + fingerprint.
     *
     * @throws PaymentConfirmationException
     * @throws CheckoutSessionConflictException
     * @throws CheckoutSessionNotOperableException
     */
    public function assertReadyForPayment(CheckoutSession $session, string $confirmedFingerprint): void;

    /*
    |--------------------------------------------------------------------------
    | Store verbs — each mutates the live cart, re-syncs the session (guarded,
    | spec 0010 §D) and returns the fresh snapshot.
    |--------------------------------------------------------------------------
    */

    public function storeContact(CheckoutSession $session, array $data): CartSnapshot;

    public function storeShippingAddress(CheckoutSession $session, array $data): CartSnapshot;

    public function storeBillingAddress(CheckoutSession $session, array $data): CartSnapshot;

    public function setShippingOption(CheckoutSession $session, string $identifier): CartSnapshot;

    public function applyCoupon(CheckoutSession $session, string $code): CartSnapshot;

    public function removeCoupon(CheckoutSession $session): CartSnapshot;

    public function associateCustomer(CheckoutSession $session, string $customerReference, ?string $email = null): CartSnapshot;

    /*
    |--------------------------------------------------------------------------
    | Read verbs — live, never persisted.
    |--------------------------------------------------------------------------
    */

    public function getShippingAddress(CheckoutSession $session): ?CheckoutAddress;

    public function getBillingAddress(CheckoutSession $session): ?CheckoutAddress;

    /**
     * @return list<array<string, mixed>>
     */
    public function getShippingOptions(CheckoutSession $session): array;

    public function getSelectedShippingOption(CheckoutSession $session): ?string;

    /**
     * @return list<array<string, mixed>>
     */
    public function getLines(CheckoutSession $session): array;

    public function getCoupon(CheckoutSession $session): ?string;
}
