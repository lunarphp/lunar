<?php

namespace Lunar\Checkout\Drivers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Validation\ValidationException;
use Lunar\Checkout\Contracts\Actions\CreatesCheckoutSession;
use Lunar\Checkout\Contracts\Actions\InvalidatesCheckoutSession;
use Lunar\Checkout\Contracts\Actions\SyncsCheckoutSession;
use Lunar\Checkout\DataObjects\CartSnapshot;
use Lunar\Checkout\DataObjects\CheckoutAddress;
use Lunar\Checkout\Events\BillingAddressStored;
use Lunar\Checkout\Events\CheckoutCompletionFailed;
use Lunar\Checkout\Events\CheckoutPaymentConfirmationFailed;
use Lunar\Checkout\Events\CheckoutSessionCompleted;
use Lunar\Checkout\Events\CouponApplied;
use Lunar\Checkout\Events\CouponRemoved;
use Lunar\Checkout\Events\CustomerAssociated;
use Lunar\Checkout\Events\ShippingAddressStored;
use Lunar\Checkout\Events\ShippingOptionSet;
use Lunar\Checkout\Exceptions\CheckoutSessionConflictException;
use Lunar\Checkout\Exceptions\CheckoutSessionNotOperableException;
use Lunar\Checkout\Exceptions\PaymentConfirmationException;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\States\CheckoutSession\Completed;
use Lunar\Checkout\States\CheckoutSession\Open;
use Lunar\Checkout\States\CheckoutSession\PaymentProcessing;
use Lunar\Core\Contracts\ShippingManifest;
use Lunar\Core\Managers\DiscountManager;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Country;

/**
 * The default checkout driver — ingests a Lunar {@see Cart} into a session,
 * mediates every cart read/write while the session is `Open`, and finalises it
 * into a Lunar order under the spec 0010 integrity model (driver-owned
 * fingerprint, guarded sync, pay-boundary pin, completion re-verify).
 */
class LunarCheckoutDriver extends AbstractCheckoutDriver
{
    public function __construct(
        private CreatesCheckoutSession $createCheckoutSession,
        private SyncsCheckoutSession $syncCheckoutSession,
        private InvalidatesCheckoutSession $invalidateCheckoutSession,
        private DiscountManager $discounts,
        private ShippingManifest $shippingManifest,
        private ConnectionInterface $db,
        private Dispatcher $events,
    ) {}

    public function createSession(mixed $source, array $attributes = []): CheckoutSession
    {
        if (! $source instanceof Cart) {
            throw new \InvalidArgumentException(
                'The lunar checkout driver expects a ['.Cart::class.'] source, ['.get_debug_type($source).'] given.'
            );
        }

        return $this->createCheckoutSession->execute(
            $source,
            $this->buildSnapshot($source->calculate()),
            $attributes,
        );
    }

    /**
     * Resume the cart's live `Open` session if one exists and has not expired;
     * otherwise fall through to {@see createSession} (which supersedes an
     * expired `Open` sibling, or refuses if one is `PaymentProcessing`).
     */
    public function resolveOrCreateSession(mixed $source, array $attributes = []): CheckoutSession
    {
        if (! $source instanceof Cart) {
            throw new \InvalidArgumentException(
                'The lunar checkout driver expects a ['.Cart::class.'] source, ['.get_debug_type($source).'] given.'
            );
        }

        $existing = CheckoutSession::query()
            ->where('cart_reference', (string) $source->id)
            ->where('status', Open::$name)
            ->first();

        if ($existing !== null && ! $existing->isExpired()) {
            return $existing;
        }

        return $this->createSession($source, $attributes);
    }

    /**
     * Spec 0010 §E.2 — re-verify, then order; no charge without an order.
     * Synchronous path (`Open`): the pay gate runs here, in the same
     * transaction as order creation, against the submitted confirmation token.
     * Async path (`PaymentProcessing`): the live cart must still match the
     * fingerprint pinned at the gate. Idempotent keyed on the session.
     */
    public function complete(CheckoutSession $session, ?string $confirmationToken = null): mixed
    {
        if ($session->status instanceof Completed) {
            return $session->order_reference;
        }

        return $this->db->transaction(function () use ($session, $confirmationToken) {
            // Consistent-read protocol: lock the cart row, load everything
            // once, verify and build the order from that same loaded set.
            $cart = Cart::query()
                ->whereKey((int) $session->cart_reference)
                ->lockForUpdate()
                ->first();

            if ($cart === null) {
                throw new CheckoutSessionNotOperableException('The session\'s source cart no longer exists.');
            }

            $cart->calculate();
            $live = $this->buildSnapshot($cart);

            $isSync = $session->status instanceof Open;

            if ($isSync) {
                if ($session->isExpired()) {
                    throw new CheckoutSessionNotOperableException('The checkout session has expired.');
                }

                if ($confirmationToken === null) {
                    throw new PaymentConfirmationException('missing_confirmation');
                }

                $this->assertPinnedContext($session, $live);
                $expected = $confirmationToken;
            } else {
                // Pinned at the pay gate; expiry never blocks completing a
                // PaymentProcessing session.
                $expected = $session->cart_fingerprint;
            }

            if (! hash_equals($expected, $live->fingerprint)) {
                $this->events->dispatch(new CheckoutCompletionFailed($session, 'verify-mismatch'));

                throw new PaymentConfirmationException('fingerprint_mismatch');
            }

            if ($isSync && ! $cart->canCreateOrder()) {
                throw new PaymentConfirmationException('cart_not_orderable');
            }

            $order = $cart->createOrder();

            $attributes = [
                'order_reference' => (string) $order->id,
                'completed_at' => now(),
                'active_cart_reference' => null,
            ];

            if ($isSync) {
                $attributes['amount_subtotal'] = $live->amountSubtotal;
                $attributes['amount_total'] = $live->amountTotal;
                $attributes['cart_fingerprint'] = $live->fingerprint;
            }

            $transitioned = $session->transitionGuarded(
                [Open::$name, PaymentProcessing::$name],
                Completed::$name,
                $attributes,
            );

            if (! $transitioned) {
                throw new CheckoutSessionConflictException('frozen');
            }

            $this->events->dispatch(new CheckoutSessionCompleted($session));

            return $order;
        });
    }

    public function snapshot(CheckoutSession $session): CartSnapshot
    {
        return $this->buildSnapshot($this->resolveCart($session)->calculate());
    }

    public function fingerprint(CheckoutSession $session): string
    {
        return $this->snapshot($session)->fingerprint;
    }

    /**
     * The pay-boundary gate (spec 0010 §E): step 0 (pinned currency/channel),
     * token match, Gate 2, then the atomic one-statement pin.
     */
    public function assertReadyForPayment(CheckoutSession $session, string $confirmedFingerprint): void
    {
        $cart = $this->resolveCart($session)->calculate();
        $live = $this->buildSnapshot($cart);

        $this->assertPinnedContext($session, $live);

        if (! hash_equals($confirmedFingerprint, $live->fingerprint)) {
            $this->events->dispatch(new CheckoutPaymentConfirmationFailed($session));

            throw new PaymentConfirmationException('fingerprint_mismatch');
        }

        if (! $cart->canCreateOrder()) {
            throw new PaymentConfirmationException('cart_not_orderable');
        }

        // The pin: one guarded statement — the expiry predicate lives here,
        // not in a pre-check (spec 0010 §E).
        $pinned = CheckoutSession::query()
            ->whereKey($session->getKey())
            ->where('status', Open::$name)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->update([
                'status' => PaymentProcessing::$name,
                'amount_subtotal' => $live->amountSubtotal,
                'amount_total' => $live->amountTotal,
                'cart_fingerprint' => $live->fingerprint,
                'payment_processing_at' => now(),
                'updated_at' => now(),
            ]);

        $session->refresh();

        if ($pinned === 1) {
            return;
        }

        if ($session->isExpired() || ! ($session->status instanceof Open || $session->status instanceof PaymentProcessing)) {
            throw new CheckoutSessionNotOperableException('The checkout session is expired or terminal.');
        }

        throw new CheckoutSessionConflictException('frozen');
    }

    /*
    |--------------------------------------------------------------------------
    | Store verbs
    |--------------------------------------------------------------------------
    */

    public function storeContact(CheckoutSession $session, array $data): CartSnapshot
    {
        $cart = $this->operableCart($session);

        if ($address = $cart->shippingAddress) {
            $address->update([
                'contact_email' => $data['email'] ?? $address->contact_email,
                'contact_phone' => $data['phone'] ?? $address->contact_phone,
            ]);
        }

        if (! empty($data['email'])) {
            $session->customer_email = $data['email'];
            $session->save();
        }

        return $this->resync($session, $cart);
    }

    public function storeShippingAddress(CheckoutSession $session, array $data): CartSnapshot
    {
        $cart = $this->operableCart($session);

        $cart->setShippingAddress($this->toCartAddressData($data));

        $snapshot = $this->resync($session, $cart);

        $this->events->dispatch(new ShippingAddressStored($session));

        return $snapshot;
    }

    public function storeBillingAddress(CheckoutSession $session, array $data): CartSnapshot
    {
        $cart = $this->operableCart($session);

        $cart->setBillingAddress($this->toCartAddressData($data));

        $snapshot = $this->resync($session, $cart);

        $this->events->dispatch(new BillingAddressStored($session));

        return $snapshot;
    }

    public function setShippingOption(CheckoutSession $session, string $identifier): CartSnapshot
    {
        $cart = $this->operableCart($session);

        $option = $this->shippingManifest->getOption($cart, $identifier);

        if ($option === null) {
            throw ValidationException::withMessages([
                'shipping_option' => 'The selected shipping option is not available.',
            ]);
        }

        $cart->setShippingOption($option);

        $snapshot = $this->resync($session, $cart);

        $this->events->dispatch(new ShippingOptionSet($session, $identifier));

        return $snapshot;
    }

    public function applyCoupon(CheckoutSession $session, string $code): CartSnapshot
    {
        $cart = $this->operableCart($session);

        if (! $this->discounts->validateCoupon($code)) {
            throw ValidationException::withMessages([
                'coupon_code' => 'The coupon code is invalid.',
            ]);
        }

        $cart->coupon_code = $code;
        $cart->save();
        $cart->calculate(force: true);

        $snapshot = $this->resync($session, $cart);

        $this->events->dispatch(new CouponApplied($session, $code));

        return $snapshot;
    }

    public function removeCoupon(CheckoutSession $session): CartSnapshot
    {
        $cart = $this->operableCart($session);

        $cart->coupon_code = null;
        $cart->save();
        $cart->calculate(force: true);

        $snapshot = $this->resync($session, $cart);

        $this->events->dispatch(new CouponRemoved($session));

        return $snapshot;
    }

    public function associateCustomer(CheckoutSession $session, string $customerReference, ?string $email = null): CartSnapshot
    {
        $cart = $this->operableCart($session);

        // Deliberately fingerprint-neutral (spec 0010 §D): identity is not a
        // payment-relevant input, so association never bounces a checkout.
        $session->customer_reference = $customerReference;

        if ($email !== null) {
            $session->customer_email = $email;
        }

        $session->save();

        $this->events->dispatch(new CustomerAssociated($session, $customerReference));

        return $this->buildSnapshot($cart);
    }

    /*
    |--------------------------------------------------------------------------
    | Read verbs
    |--------------------------------------------------------------------------
    */

    public function getShippingAddress(CheckoutSession $session): ?CheckoutAddress
    {
        return $this->toCheckoutAddress($this->resolveCart($session)->shippingAddress);
    }

    public function getBillingAddress(CheckoutSession $session): ?CheckoutAddress
    {
        return $this->toCheckoutAddress($this->resolveCart($session)->billingAddress);
    }

    public function getShippingOptions(CheckoutSession $session): array
    {
        $cart = $this->resolveCart($session)->calculate();

        return $this->shippingManifest->getOptions($cart)
            ->map(fn ($option): array => [
                'identifier' => $option->identifier,
                'name' => $option->name,
                'description' => $option->description,
                'price' => $option->getPrice()->value,
                'collect' => $option->collect,
            ])
            ->values()
            ->all();
    }

    public function getSelectedShippingOption(CheckoutSession $session): ?string
    {
        return $this->resolveCart($session)->shippingAddress?->shipping_option;
    }

    public function getLines(CheckoutSession $session): array
    {
        $cart = $this->resolveCart($session)->calculate();

        return $cart->lines
            ->map(fn ($line): array => [
                'identifier' => (string) $line->id,
                'description' => $line->purchasable?->getDescription(),
                'quantity' => $line->quantity,
                'unit_price' => $line->unitPrice?->value,
                'sub_total' => $line->subTotal?->value,
                'total' => $line->total?->value,
            ])
            ->values()
            ->all();
    }

    public function getCoupon(CheckoutSession $session): ?string
    {
        return $this->resolveCart($session)->coupon_code;
    }

    public function getTotals(CheckoutSession $session): array
    {
        $cart = $this->resolveCart($session)->calculate();

        return [
            'sub_total' => $cart->subTotal?->value ?? 0,
            'discount_total' => $cart->discountTotal?->value ?? 0,
            'shipping_total' => $cart->shippingTotal?->value ?? 0,
            'tax_total' => $cart->taxTotal?->value ?? 0,
            'total' => $cart->total?->value ?? 0,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    private function resolveCart(CheckoutSession $session): Cart
    {
        $cart = Cart::query()->find((int) $session->cart_reference);

        if ($cart === null) {
            throw new CheckoutSessionNotOperableException('The session\'s source cart no longer exists.');
        }

        return $cart;
    }

    /**
     * Store verbs only operate while `Open` and unexpired: PaymentProcessing
     * is frozen (409), terminal/expired is gone (410).
     */
    private function operableCart(CheckoutSession $session): Cart
    {
        if ($session->status instanceof PaymentProcessing) {
            throw new CheckoutSessionConflictException('frozen');
        }

        if (! $session->status instanceof Open || $session->isExpired()) {
            throw new CheckoutSessionNotOperableException('The checkout session is expired or terminal.');
        }

        return $this->resolveCart($session)->calculate();
    }

    private function resync(CheckoutSession $session, Cart $cart): CartSnapshot
    {
        $snapshot = $this->buildSnapshot($cart->calculate(force: true));

        $this->syncCheckoutSession->execute($session, $snapshot);

        return $snapshot;
    }

    private function buildSnapshot(Cart $cart): CartSnapshot
    {
        return new CartSnapshot(
            amountSubtotal: $cart->subTotal?->value ?? 0,
            amountTotal: $cart->total?->value ?? 0,
            currencyCode: $cart->currency->code,
            channelHandle: $this->channelHandle($cart),
            fingerprint: $this->computeFingerprint($cart),
            hasAppliedDiscount: ($cart->discountTotal?->value ?? 0) > 0,
            couponCode: $cart->coupon_code,
        );
    }

    /**
     * The cart has no channel relation in v2 — resolve the handle from the
     * FK once per driver instance.
     */
    private function channelHandle(Cart $cart): string
    {
        return Channel::query()->whereKey($cart->channel_id)->value('handle') ?? 'default';
    }

    /**
     * The driver-owned integrity fingerprint (spec 0010 §D): an HMAC over a
     * structured payload covering line content, address identity, the selected
     * shipping option, the coupon, the payable total and the currency. Customer
     * identity is deliberately excluded; this is NOT `Cart::fingerprint()` and
     * is never resolved through the swappable `GeneratesFingerprint` binding.
     */
    private function computeFingerprint(Cart $cart): string
    {
        $payload = [
            'lines' => $cart->lines
                ->map(fn ($line): array => [
                    'type' => $line->purchasable_type,
                    'id' => (string) $line->purchasable_id,
                    'quantity' => $line->quantity,
                    'sub_total' => $line->subTotal?->value,
                ])
                ->values()
                ->all(),
            'shipping_address' => $this->addressIdentity($cart->shippingAddress),
            'billing_address' => $this->addressIdentity($cart->billingAddress),
            'shipping_option' => $cart->shippingAddress?->shipping_option,
            'coupon' => $cart->coupon_code,
            'amount_total' => $cart->total?->value ?? 0,
            'currency' => $cart->currency->code,
        ];

        return hash_hmac('sha256', (string) json_encode($payload), (string) config('app.key'));
    }

    /**
     * The address fields that identify WHERE the order goes — contact details
     * are excluded (payment-neutral).
     */
    private function addressIdentity(?CartAddress $address): ?array
    {
        if ($address === null) {
            return null;
        }

        return [
            'first_name' => $address->first_name,
            'last_name' => $address->last_name,
            'company_name' => $address->company_name,
            'line_one' => $address->line_one,
            'line_two' => $address->line_two,
            'line_three' => $address->line_three,
            'city' => $address->city,
            'state' => $address->state,
            'postcode' => $address->postcode,
            'country_id' => $address->country_id,
        ];
    }

    /**
     * Gate step 0 (spec 0010 §E): live currency/channel must equal the pinned
     * values — divergence invalidates (void-first) and rejects.
     */
    private function assertPinnedContext(CheckoutSession $session, CartSnapshot $live): void
    {
        if ($live->currencyCode === $session->currency_code
            && $live->channelHandle === $session->channel_handle) {
            return;
        }

        $this->invalidateCheckoutSession->execute($session, 'context_diverged');

        throw new PaymentConfirmationException('context_diverged');
    }

    /**
     * Inverse of {@see toCheckoutAddress()}: the backend-neutral address
     * payload (spec 0010 §B) mapped onto Lunar's CartAddress columns. Absent
     * keys are dropped so partial updates never null-out stored fields.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function toCartAddressData(array $data): array
    {
        $countryCode = $data['country_code'] ?? null;

        return array_filter([
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'line_one' => $data['line1'] ?? null,
            'line_two' => $data['line2'] ?? null,
            'line_three' => $data['line3'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'postcode' => $data['postcode'] ?? null,
            'contact_phone' => $data['phone'] ?? null,
            'contact_email' => $data['email'] ?? null,
            'country_id' => is_string($countryCode)
                ? Country::query()->where('iso2', strtoupper($countryCode))->value('id')
                : null,
        ], fn (mixed $value): bool => $value !== null);
    }

    private function toCheckoutAddress(?CartAddress $address): ?CheckoutAddress
    {
        if ($address === null) {
            return null;
        }

        return new CheckoutAddress(
            countryCode: $address->country?->iso2 ?? '',
            firstName: $address->first_name,
            lastName: $address->last_name,
            companyName: $address->company_name,
            line1: $address->line_one,
            line2: $address->line_two,
            line3: $address->line_three,
            city: $address->city,
            state: $address->state,
            postcode: $address->postcode,
            phone: $address->contact_phone,
            email: $address->contact_email,
        );
    }
}
