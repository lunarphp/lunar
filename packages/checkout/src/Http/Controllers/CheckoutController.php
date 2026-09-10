<?php

namespace Lunar\Checkout\Http\Controllers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Checkout\Contracts\Actions\ReconcilesCheckoutSession;
use Lunar\Checkout\Contracts\AddressLookup;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Contracts\CheckoutElement;
use Lunar\Checkout\Contracts\DeliveryCountries;
use Lunar\Checkout\Contracts\DeliveryNotice;
use Lunar\Checkout\Contracts\ElementRegistry;
use Lunar\Checkout\Contracts\GuardsPayment;
use Lunar\Checkout\Contracts\PaymentMethod;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Checkout\DataObjects\CheckoutAddress;
use Lunar\Checkout\DataObjects\CheckoutTheme;
use Lunar\Checkout\Events\CheckoutElementStored;
use Lunar\Checkout\Exceptions\AddressLookupException;
use Lunar\Checkout\Exceptions\PaymentConfirmationException;
use Lunar\Checkout\Exceptions\RollbackQuote;
use Lunar\Checkout\Express;
use Lunar\Checkout\Models\CheckoutSession as CheckoutSessionModel;
use Lunar\Checkout\Session\ModelElementStore;
use Lunar\Checkout\States\CheckoutSession\Cancelled;
use Lunar\Checkout\States\CheckoutSession\Completed;
use Lunar\Checkout\States\CheckoutSession\Open;
use Lunar\Checkout\States\CheckoutSession\PaymentProcessing;
use Lunar\Checkout\Support\PickupPoints;
use Lunar\Core\Contracts\CreatesPaymentIntents;
use Lunar\Core\Contracts\SupportsPaymentHolds;
use Lunar\Core\Contracts\SupportsPaymentIntents;
use Lunar\Core\Contracts\SyncsPaymentIntents;
use Lunar\Core\DataObjects\HoldDescription;
use Lunar\Core\Enums\HoldAdjustment;
use Lunar\Core\Enums\PaymentIntentStatus;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Order;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CheckoutController extends Controller
{
    /**
     * Collaborators are constructor-injected per the service-layer DI rule
     * (spec 0016): the element registry and the address lookup are bound to
     * their contracts in the service provider; the dispatcher is the
     * framework's own event dispatcher.
     */
    public function __construct(
        private readonly ElementRegistry $registry,
        private readonly AddressLookup $addressLookup,
        private readonly Dispatcher $events,
    ) {}

    /**
     * Start (or resume) a checkout for the current cart and redirect to its
     * UUID URL. Session creation is a POST-only mutation — never a side effect
     * of loading the cart or hitting a bare GET (spec 0004 §F). `resolveOrCreate`
     * resumes the cart's live `Open` session, so re-pressing "Checkout" lands
     * back on the same session rather than churning a new one.
     */
    public function start(Request $request, CheckoutDriver $checkoutDriver): SymfonyResponse
    {
        $cart = CartSession::current();

        // No cart, or a cart with nothing in it, cannot produce an order —
        // bounce back to wherever the request came from (the cart page),
        // never a hard 500 and never an empty checkout. The storefront hides
        // the CTA for empty baskets, but a stale tab or crafted POST still
        // lands here. A zero-TOTAL cart with lines remains valid (spec 0010).
        if ($cart === null || ! $cart->lines()->exists()) {
            return redirect()->back()->with('lunar.checkout.error', $this->checkoutError(
                'cart_empty',
                'Your basket is empty, so there is nothing to check out.',
                'view_basket',
            ));
        }

        $session = $checkoutDriver->resolveOrCreateSession($cart);

        // A host-page caller (the express wallet mount, spec 0012 SF) mints
        // the session lazily on first wallet interaction and needs the raw
        // session urls back, not a page navigation: it never leaves the host
        // page, so Inertia::location (which forces one) does not apply here.
        if ($request->expectsJson()) {
            return response()->json([
                'uuid' => $session->uuid,
                'fulfilment' => $checkoutDriver->getFulfilment($session),
                'pickupPoints' => $checkoutDriver->getPickupPoints($session),
                'pickupPointId' => $checkoutDriver->getSelectedPickupPoint($session),
                'urls' => Arr::only(
                    array_merge($this->sessionUrls($session), [
                        'contact' => route('lunar.checkout.contact.store', $session->uuid),
                    ]),
                    ['quote', 'paymentIntent', 'shippingAddress', 'billingAddress', 'shippingOption', 'fulfilment', 'pickupPoint', 'confirm', 'contact'],
                ),
            ]);
        }

        // The session URL renders the checkout's OWN Inertia app (its own root
        // view + bundle). That's a different Inertia app to the consumer's, so
        // an in-app XHR visit can't cross into it — Inertia::location forces a
        // full-page navigation for Inertia callers (409 + X-Inertia-Location)
        // and a plain 302 for everyone else (spec 0008 §A).
        return Inertia::location(route('lunar.checkout.show', $session->uuid));
    }

    /**
     * Render the checkout against the package's OWN Inertia root view — the
     * checkout is a self-contained app, not a guest in the consumer's Inertia
     * setup (spec 0008 §A). Data arrives as no-store props, so no PII is baked
     * into cacheable HTML.
     *
     * The session is resolved from its UUID (route-model bound). The UUID is a
     * capability token, so ownership is verified before any session data is
     * projected, and terminal sessions redirect rather than render.
     *
     * Return type is widened past Response|RedirectResponse: a cart-swap
     * reconcile hands off to Inertia::location(), which for an Inertia XHR
     * caller returns a bare 409 SymfonyResponse rather than a RedirectResponse
     * (same reason `start()` above is typed against SymfonyResponse).
     */
    public function show(CheckoutSessionModel $session, CheckoutDriver $checkoutDriver, CheckoutTheme $theme): Response|RedirectResponse|SymfonyResponse
    {
        $this->ensureOwnership($session);

        /*
         * A pinned session normally completes via the gateway webhook, but
         * webhooks can be slow, misconfigured or absent (local dev). The
         * customer polling this page is the other reliable signal, so settle
         * against the gateway's actual outcome here: captured completes,
         * failed reopens, in-flight stays pinned. The short age gate keeps the
         * immediate post-confirm poll from racing the webhook, and complete()
         * is idempotent either way. Failures are swallowed: this render must
         * never die on a gateway blip.
         */
        if ($session->status instanceof PaymentProcessing
            && $session->payment_processing_at?->lt(now()->subSeconds(
                (int) config('lunar.checkout.reconciliation.on_view_after_seconds', 5),
            ))
        ) {
            try {
                app(ReconcilesCheckoutSession::class)->execute($session);
                $session->refresh();
            } catch (\Throwable $e) {
                // Reconciliation sweep owns whatever this could not settle,
                // but the failure must not vanish.
                report($e);
            }
        }

        // Order already placed: send to the stored return URL if the caller
        // set one (hosted flow), else the configured store success URL. This
        // runs BEFORE the live-cart check below: once the order exists the
        // customer's live cart is a fresh empty one, and a reload of this URL
        // (the synchronous pay path does exactly that) must land on the
        // confirmation, not bounce to an empty basket.
        if ($session->status instanceof Completed) {
            return $this->redirectToSuccess($session);
        }

        // A dead capability token (expired window / cancelled) can't render a
        // live checkout. Bounce home; the customer restarts from the cart.
        if ($session->isExpired() || $session->status instanceof Cancelled) {
            return $this->redirectDeadSession($session);
        }

        // A login mid-checkout can merge/swap the live cart (auth_policy=merge).
        // cart_reference is pinned identity, so rather than mutate it, re-resolve
        // to the surviving cart's session and move the customer there. Guarded to
        // signed-in owners; a guest with a different cart already failed ownership.
        if ($user = auth()->user()) {
            $cart = CartSession::current();

            if ($cart !== null && (string) $cart->id !== $session->cart_reference) {
                // The live cart is a different one (a sign-in merge, or a new
                // cart minted after this session's order completed). If it is
                // EMPTY there is nothing to move the customer onto — minting a
                // session for it would render a £0.00 checkout that cannot
                // pay. Back to the basket, with the reason attached.
                if (! $cart->lines()->exists()) {
                    return redirect()->to($this->cancelUrl($session))->with('lunar.checkout.error', $this->checkoutError(
                        'cart_empty',
                        'Your basket is empty, so there is nothing to check out.',
                        'view_basket',
                    ));
                }

                $fresh = $checkoutDriver->resolveOrCreateSession($cart);

                if ($fresh->uuid !== $session->uuid) {
                    return Inertia::location(route('lunar.checkout.show', $fresh->uuid));
                }
            }

            // Associate once (idempotent — only when it changes) so we don't spam
            // CustomerAssociated on every render.
            $customerId = (string) ($user->latestCustomer()?->id ?? '');

            if ($customerId !== '' && $session->customer_reference !== $customerId) {
                $checkoutDriver->associateCustomer($session, $customerId, $user->email);
            }
        }

        /*
         * The session's own cart can be emptied from another tab mid-checkout,
         * and nothing empty may render as a payable checkout. Same exit as an
         * expired session: the basket, with the reason attached.
         */
        $cart = Cart::query()->find((int) $session->cart_reference);

        if ($cart === null || ! $cart->lines()->exists()) {
            return redirect()->to($this->cancelUrl($session))->with('lunar.checkout.error', $this->checkoutError(
                'cart_empty',
                'Your basket is empty, so there is nothing to check out.',
                'view_basket',
            ));
        }

        Inertia::setRootView('lunar-checkout::app');

        return Inertia::render('Show', [
            'checkout' => array_merge(
                $this->projectCheckout($checkoutDriver, $session),
                ['elements' => $this->projectElements($session)],
            ),
            'theme' => $theme->tokens(),
            'branding' => $theme->branding(),
            // Consumer override stylesheet; the root view injects it as a <link>
            // after the checkout's own CSS (see lunar-checkout::app).
            'stylesheet' => $theme->stylesheet(),
            'favicon' => $theme->favicon(),
        ]);
    }

    /**
     * The express confirm ("squeeze") page (spec 0012 §D): the wallet sheet
     * authorised a hold, this page reviews it and captures only at "Confirm &
     * pay". Mirrors show()'s ownership + render mechanics exactly; only the
     * guard and the extra `hold` prop differ.
     *
     * Unreachable without a live, gateway-verified hold: any guard failure
     * (no intent, a non-hold intent, or the gateway not confirming
     * requires-capture) redirects to the normal show(), which itself owns
     * every terminal-session redirect (Completed/PaymentProcessing/Expired).
     * `liveHold()` only ever returns non-null for an `Open` session, so those
     * states always fall into this same redirect.
     */
    public function confirm(CheckoutSessionModel $session, CheckoutDriver $checkoutDriver, CheckoutTheme $theme): Response|RedirectResponse|SymfonyResponse
    {
        $this->ensureOwnership($session);

        $hold = $this->liveHold($session);

        if ($hold === null) {
            return redirect()->route('lunar.checkout.show', $session->uuid);
        }

        Inertia::setRootView('lunar-checkout::app');

        return Inertia::render('ExpressConfirm', [
            'checkout' => array_merge(
                $this->projectCheckout($checkoutDriver, $session),
                [
                    'elements' => $this->projectElements($session),
                    'hold' => [
                        'amountAuthorised' => $hold->amountMinor,
                        'walletLabel' => $hold->walletLabel,
                    ],
                ],
            ),
            'theme' => $theme->tokens(),
            'branding' => $theme->branding(),
            'stylesheet' => $theme->stylesheet(),
            'favicon' => $theme->favicon(),
        ]);
    }

    /**
     * The session's hold, verified against the gateway (never the client's
     * claim): non-null only when the session is `Open` and not expired, its
     * intent is hold-flavoured, and the gateway reports it authorised and
     * awaiting capture (spec 0012 §D render guard).
     *
     * Expiry is swept lazily, so `isExpired()` (expires_at in the past, not
     * just the `Expired` state) must be checked alongside `Open` here, the
     * same combined check `show()` and `processing()` already use elsewhere
     * in this controller: a session whose window has closed but whose row
     * has not yet been transitioned still reads `Open` and must not render.
     */
    private function liveHold(CheckoutSessionModel $session): ?HoldDescription
    {
        if (! $session->status instanceof Open || $session->isExpired() || ! $session->isHoldMode() || $session->payment_intent_ref === null) {
            return null;
        }

        $gateway = Payments::driver((string) ($session->meta['payment_method'] ?? ''));

        if (! $gateway instanceof SupportsPaymentHolds) {
            return null;
        }

        try {
            $description = $gateway->describeHold($session->payment_intent_ref);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        return $description?->status === PaymentIntentStatus::RequiresCapture ? $description : null;
    }

    /**
     * Ownership says who may touch the session; this says whether it is still
     * touchable at all. `show()` tests the same three terminal conditions and
     * redirects a browser out of them; an XHR write has nowhere to redirect
     * to, so it gets a 409 rather than quietly mutating a session whose order
     * is already placed or whose window has closed.
     */
    private function ensureOperable(CheckoutSessionModel $session): void
    {
        abort_if(
            $session->status instanceof Completed
                || $session->status instanceof Cancelled
                || $session->isExpired(),
            409,
        );
    }

    /**
     * The UUID in the URL is a capability token. Without an ownership check any
     * leaked or guessed UUID would expose the session's PII (email, addresses),
     * so the requester must own the session: their live cart is its source, or
     * the customer associated with the session if the cart ID differs (e.g. during
     * login cart-merge). A mismatch is abuse, not a wrong turn — 403, not a redirect.
     */
    private function ensureOwnership(CheckoutSessionModel $session): void
    {
        $cart = CartSession::current();

        if ($cart !== null && (string) $cart->id === $session->cart_reference) {
            return;
        }

        // Fallback: a signed-in customer owns their session even if the live
        // cart id differs (e.g. Lunar's login cart-merge swapped it). Keyed on
        // the customer, so a leaked UUID for a different account is still 403.
        $customerId = auth()->user()?->latestCustomer()?->id;

        abort_unless(
            $customerId !== null && (string) $customerId === $session->customer_reference,
            403,
        );
    }

    /**
     * Project the live checkout session into the prop shape the Vue app
     * consumes. Cart figures are read live (read verbs never persist); money
     * values are minor units. The client pricing engine still derives the
     * breakdown from these (spec 0004); a server-driven CheckoutData breakdown
     * replaces it once spec 0001 lands.
     *
     * @return array<string, mixed>
     */
    private function projectCheckout(CheckoutDriver $driver, CheckoutSessionModel $session): array
    {
        $snapshot = $driver->snapshot($session);
        $cart = Cart::query()->findOrFail((int) $session->cart_reference);

        return [
            'uuid' => $session->uuid,
            'merchant' => config('checkout.merchant') ?: config('app.name'),
            'currency' => $snapshot->currencyCode,
            'items' => array_map(fn (array $line): array => [
                'id' => $line['identifier'],
                'title' => $line['description'],
                'sku' => $line['sku'] ?? null,
                'image' => $line['image'] ?? null,
                'qty' => $line['quantity'],
                'price' => $line['unit_price'] ?? 0,
            ], $driver->getLines($session)),
            'shippingMethods' => array_map(fn (array $option): array => [
                'id' => $option['identifier'],
                'name' => $option['name'],
                'sub' => $option['description'],
                'price' => $option['price'] ?? 0,
                'collect' => (bool) ($option['collect'] ?? false),
            ], $driver->getShippingOptions($session)),
            'shippingId' => $driver->getSelectedShippingOption($session),
            // Spec 0013 §E. The mode is server state (a reload must not fall
            // back to delivery while the cart holds the collect option); the
            // points are the host's; the selection is the cart's.
            'fulfilment' => $driver->getFulfilment($session),
            'pickupPoints' => $driver->getPickupPoints($session),
            'pickupPointId' => $driver->getSelectedPickupPoint($session),
            // Where the customer is, when the host can say, so the branch
            // list can lead with the nearest; the unit is the host's choice
            // or, null, follows the delivery country in the browser.
            'pickupOrigin' => PickupPoints::origin($cart)?->toArray(),
            'distanceUnit' => config('lunar.checkout.pickup.distance_unit'),
            'shippingAddress' => $driver->getShippingAddress($session),
            // Null until the customer captures one of their own or pay()
            // copies the delivery address across; the frontend reads a
            // stored address that differs from delivery as "not the same".
            'billingAddress' => $driver->getBillingAddress($session),
            'savedAddresses' => $this->projectSavedAddresses(),
            // Spec 0011 §H: the only countries the delivery step offers, and
            // the host's word on why delivery may be missing for this cart.
            'countries' => $this->projectCountries($cart),
            'deliveryNotice' => app()->bound(DeliveryNotice::class) ? app(DeliveryNotice::class)->noticeFor($cart) : null,
            'totals' => $driver->getTotals($session),
            'coupon' => $driver->getCoupon($session),
            // The pay boundary echoes back the fingerprint of the state the
            // customer confirmed (spec 0010 §E).
            'fingerprint' => $driver->fingerprint($session),
            'paymentMethods' => array_map(fn (PaymentMethod $method): array => [
                'handle' => $method->handle(),
                'label' => $method->label(),
                'driver' => $method->driver(),
                'requiresIntent' => $method->requiresIntent(),
                'component' => $method->component(),
                'config' => $method->config(),
                'paymentBlocker' => $method instanceof GuardsPayment ? $method->paymentBlocker($session, $cart) : null,
                // A method's own claim is never trusted alone: the wallet
                // region only renders for a driver that can actually hold
                // (spec 0012 SF; see Express::driverSupportsHolds()).
                'supportsExpress' => $method->supportsExpress() && Express::driverSupportsHolds($method),
                'expressComponent' => $method->expressComponent(),
            ], app(PaymentMethodRegistry::class)->availableFor($cart)),
            // Why a registered method is missing, in the customer's words
            // (spec 0002 §B): shown when the region would otherwise be empty.
            'paymentUnavailable' => array_values(app(PaymentMethodRegistry::class)->unavailableReasons($cart)),
            'urls' => $this->sessionUrls($session),
        ];
    }

    /**
     * The session's write/navigation endpoints the checkout Vue app (and the
     * host-page express mount's JSON start response) drive it through.
     *
     * @return array<string, string|null>
     */
    private function sessionUrls(CheckoutSessionModel $session): array
    {
        return [
            'show' => route('lunar.checkout.show', $session->uuid),
            'shippingAddress' => route('lunar.checkout.shipping-address.store', $session->uuid),
            'billingAddress' => route('lunar.checkout.billing-address.store', $session->uuid),
            'shippingOption' => route('lunar.checkout.shipping-option.store', $session->uuid),
            'fulfilment' => route('lunar.checkout.fulfilment.store', $session->uuid),
            'pickupPoint' => route('lunar.checkout.pickup-point.store', $session->uuid),
            'paymentIntent' => route('lunar.checkout.payment-intent.store', $session->uuid),
            'pay' => route('lunar.checkout.pay', $session->uuid),
            'paymentRelease' => route('lunar.checkout.payment-release', $session->uuid),
            'processing' => route('lunar.checkout.processing', $session->uuid),
            // The express confirm ("squeeze") page, and the non-persisting
            // shipping-rate quote it (and the wallet sheet) use to price an
            // edit before committing it (spec 0012 §C/§D).
            'confirm' => route('lunar.checkout.confirm', $session->uuid),
            'quote' => route('lunar.checkout.shipping-quote', $session->uuid),
            // Escape hatch back to the store (the basket, usually):
            // session cancel_url, then the store-wide config default.
            'back' => $this->cancelUrl($session),
            // Null when no driver can answer, which is how the delivery
            // step knows to render manual entry instead of a dead search.
            'addressLookup' => $this->addressLookup->isAvailable()
                ? route('lunar.checkout.address-lookup', $session->uuid)
                : null,
        ];
    }

    /**
     * Validate and persist one element's captured data into the checkout
     * session. The element owns its rules and its write path (the prototype
     * writes to the session; the spec 0001 model writes through the cart API).
     */
    public function storeElement(Request $request, CheckoutSessionModel $session, string $handle): RedirectResponse
    {
        $this->ensureOwnership($session);
        $this->ensureOperable($session);

        $element = $this->registry->get($handle);

        abort_if($element === null, 404);

        $element->setDataStore(new ModelElementStore($session));

        $validated = $request->validate($element->rules());

        $element->store($validated);

        $this->events->dispatch(new CheckoutElementStored($session, $handle, $validated));

        return back();
    }

    /**
     * Does this email belong to an existing account? Owned + rate-limited, and
     * deliberately returns nothing but a boolean — passkey presence is revealed
     * only by the sign-in ceremony, never here.
     */
    public function contactLookup(Request $request, CheckoutSessionModel $session): JsonResponse
    {
        $this->ensureOwnership($session);

        $data = $request->validate(['email' => ['required', 'email']]);

        $exists = Auth::getProvider()->retrieveByCredentials(['email' => $data['email']]) !== null;

        return response()->json(['exists' => $exists]);
    }

    /**
     * Addresses at a postcode (spec 0011 §C). Owned and throttled, and the
     * postcode is validated before the vendor is touched so a malformed value
     * can never cost the merchant a billed lookup.
     *
     * Selecting a returned address fills the delivery form client-side; it does
     * not write the cart. Persistence stays on the shipping-address route, so
     * address writes keep one path.
     */
    public function addressLookup(Request $request, CheckoutSessionModel $session): JsonResponse
    {
        $this->ensureOwnership($session);
        $this->ensureOperable($session);

        $data = $request->validate([
            'postcode' => ['required', 'string', 'max:12', 'regex:/^[A-Za-z]{1,2}\d[A-Za-z\d]?\s*\d[A-Za-z]{2}$/'],
        ]);

        try {
            $addresses = $this->addressLookup->lookup($data['postcode']);
        } catch (AddressLookupException) {
            // The vendor's own message never reaches the browser.
            return response()->json(['message' => 'We could not search for that postcode. Enter your address manually.'], 503);
        }

        return response()->json(['addresses' => $addresses]);
    }

    /**
     * Quote shipping rates and totals for a candidate address (and optionally a
     * candidate option) WITHOUT persisting either (spec 0012 SC). Runs the real
     * driver writes inside a transaction that always rolls back: one code path
     * with the store routes, zero risk of divergence, nothing written.
     */
    public function quoteShippingRates(Request $request, CheckoutSessionModel $session, CheckoutDriver $checkoutDriver): JsonResponse
    {
        $this->ensureOwnership($session);

        $data = $request->validate([
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:12'],
            'country_code' => ['required', 'string', Rule::exists(Country::class, 'iso2')],
            'shipping_option' => ['nullable', 'string'],
        ]);

        $quote = null;

        try {
            DB::transaction(function () use ($session, $checkoutDriver, $data, &$quote): void {
                $checkoutDriver->storeShippingAddress($session, [
                    'first_name' => 'Quote',
                    'last_name' => 'Quote',
                    'line1' => 'Quote',
                    'city' => $data['city'] ?? '',
                    'state' => $data['state'] ?? null,
                    'postcode' => $data['postcode'],
                    'country_code' => $data['country_code'],
                ]);

                $options = $checkoutDriver->getShippingOptions($session);

                // The Express Checkout Element auto-selects the first rate the
                // sheet shows without firing shippingratechange for it, so a
                // quote with no chosen option must price totals as if that
                // first deliverable option were selected; otherwise the sheet
                // authorises a total excluding the shipping it displays.
                $chosen = $data['shipping_option'] ?? null;

                if (empty($chosen)) {
                    $chosen = collect($options)->firstWhere('collect', false)['identifier'] ?? null;
                }

                if (! empty($chosen)) {
                    $checkoutDriver->setShippingOption($session, $chosen);
                }

                $quote = [
                    'methods' => array_map(fn (array $option): array => [
                        'id' => $option['identifier'],
                        'name' => $option['name'],
                        'sub' => $option['description'],
                        'price' => $option['price'] ?? 0,
                        'collect' => (bool) ($option['collect'] ?? false),
                    ], $options),
                    'totals' => $checkoutDriver->getTotals($session),
                ];

                throw new RollbackQuote;
            });
        } catch (RollbackQuote) {
            // Deliberate: the transaction exists to be rolled back.
        }

        return response()->json($quote);
    }

    /**
     * Persist the contact email. Guest → customer_email on the model; signed-in
     * → associate the customer (which also stores the email). Order attachment
     * itself rides on Lunar's cart↔user link, not this value.
     */
    public function storeContact(Request $request, CheckoutSessionModel $session, CheckoutDriver $checkoutDriver): RedirectResponse
    {
        $this->ensureOwnership($session);

        $data = $request->validate(['email' => ['required', 'email']]);

        $customerId = auth()->user()?->latestCustomer()?->id;

        if ($customerId !== null) {
            $checkoutDriver->associateCustomer($session, (string) $customerId, $data['email']);
        } else {
            $session->customer_email = $data['email'];
            $session->save();
        }

        return back();
    }

    /**
     * Store the delivery address on the cart through the driver. The payload
     * is the backend-neutral address shape (spec 0010 §B); shipping options
     * are address-dependent, so the next render re-projects them fresh.
     */
    public function storeShippingAddress(Request $request, CheckoutSessionModel $session, CheckoutDriver $checkoutDriver): RedirectResponse
    {
        $this->ensureOwnership($session);

        $cart = Cart::query()->findOrFail((int) $session->cart_reference);

        $data = $request->validate(
            $this->addressRules(array_column($this->projectCountries($cart), 'code')),
            ['country_code.in' => 'We do not deliver to that country.'],
        );

        $checkoutDriver->storeShippingAddress($session, $data);
        $this->touchSavedAddress($data);

        return back();
    }

    /**
     * Track address-book usage: when the stored delivery address matches one
     * of the signed-in customer's saved addresses, stamp it so the book leads
     * with "last used" next time (the projection orders by last_used_at
     * first). Line one + postcode is the same identity the frontend picker
     * uses; a card selection posts those fields verbatim, so the match holds.
     *
     * @param  array<string, mixed>  $data
     */
    private function touchSavedAddress(array $data): void
    {
        $customer = auth()->user()?->latestCustomer();

        if ($customer === null) {
            return;
        }

        $customer->addresses()
            ->where('line_one', $data['line1'])
            ->where('postcode', $data['postcode'])
            ->update(['last_used_at' => now()]);
    }

    /**
     * The signed-in customer's address book, mapped to the same backend-neutral
     * shape the cart address round-trips in (spec 0010 §B), so the delivery
     * step can offer one-click selection. Selecting one still writes through
     * the shipping-address route: this is a read-only projection, never a
     * second write path. Guests get an empty list. Lunar's Address model is
     * customer domain rather than cart domain, which is why this lives here
     * beside ensureOwnership() instead of on the CheckoutDriver.
     *
     * @return array<int, array{id: string, title: string|null, shippingDefault: bool, address: CheckoutAddress}>
     */
    private function projectSavedAddresses(): array
    {
        $customer = auth()->user()?->latestCustomer();

        if ($customer === null) {
            return [];
        }

        return $customer->addresses()
            ->with('country')
            ->orderByDesc('last_used_at')
            ->orderByDesc('shipping_default')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(fn (Address $address): array => [
                'id' => $address->public_id,
                'title' => $address->title,
                'shippingDefault' => (bool) $address->shipping_default,
                'address' => new CheckoutAddress(
                    countryCode: $address->country?->iso2 ?? 'GB',
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
                ),
            ])
            ->all();
    }

    /**
     * The backend-neutral address payload (spec 0010 §B), shared by the
     * shipping and billing stores. The shipping store passes the delivery
     * countries so an address the store cannot ship to is refused on save;
     * a billing address may be anywhere a card is registered, so it only
     * has to be a real country.
     *
     * @param  list<string>|null  $countryCodes
     * @return array<string, array<int, mixed>>
     */
    private function addressRules(?array $countryCodes = null): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:12'],
            'country_code' => [
                'required',
                'string',
                $countryCodes === null ? Rule::exists(Country::class, 'iso2') : Rule::in($countryCodes),
            ],
            'phone' => ['nullable', 'string', 'max:32'],
        ];
    }

    /**
     * The delivery countries as the frontend select wants them (spec 0011 §H).
     *
     * @return list<array{code: string, name: string}>
     */
    private function projectCountries(Cart $cart): array
    {
        return app(DeliveryCountries::class)
            ->available($cart)
            ->map(fn (Country $country): array => [
                'code' => $country->iso2,
                'name' => $country->name,
            ])
            ->values()
            ->all();
    }

    /**
     * Select a shipping option for the cart. The driver validates the
     * identifier against the live shipping manifest, so an option a cart
     * modifier withheld (exclusion lists, oversized blocklists) is rejected
     * with a validation error rather than silently accepted.
     */
    public function storeShippingOption(Request $request, CheckoutSessionModel $session, CheckoutDriver $checkoutDriver): RedirectResponse
    {
        $this->ensureOwnership($session);

        $data = $request->validate(['shipping_option' => ['required', 'string']]);

        $checkoutDriver->setShippingOption($session, $data['shipping_option']);

        return back();
    }

    /**
     * Record the fulfilment mode (delivery / collect), spec 0013 §E. Cart
     * state written through the driver, same shape as storeShippingOption
     * above: ownership only, no ensureOperable (the driver's operableCart()
     * already covers a frozen/terminal session).
     */
    public function storeFulfilment(Request $request, CheckoutSessionModel $session, CheckoutDriver $checkoutDriver): RedirectResponse
    {
        $this->ensureOwnership($session);

        $data = $request->validate(['fulfilment' => ['required', 'string', 'in:delivery,collect']]);

        $checkoutDriver->setFulfilment($session, $data['fulfilment']);

        return back();
    }

    /**
     * Record which pickup point the customer picked, spec 0013 §E. The
     * driver validates the handle against the host's offered points before
     * writing it to the cart.
     */
    public function storePickupPoint(Request $request, CheckoutSessionModel $session, CheckoutDriver $checkoutDriver): RedirectResponse
    {
        $this->ensureOwnership($session);

        $data = $request->validate(['pickup_point' => ['required', 'string']]);

        $checkoutDriver->setPickupPoint($session, $data['pickup_point']);

        return back();
    }

    /**
     * Store the billing address through the driver — same neutral payload as
     * the shipping address; the frontend defaults it to the delivery address.
     */
    public function storeBillingAddress(Request $request, CheckoutSessionModel $session, CheckoutDriver $checkoutDriver): JsonResponse|RedirectResponse
    {
        $this->ensureOwnership($session);

        $data = $request->validate([
            ...$this->addressRules(),
            'fingerprint' => ['nullable', 'string'],
        ]);

        /*
         * The pay() flow writes the billing address moments before pinning
         * and then pins against the post-write fingerprint handed back below.
         * Without this check that hand-back would launder any change made
         * elsewhere (a line added in another tab) into a matching pin, and the
         * customer would be charged a total they never saw. So the caller
         * states which cart it is looking at, and a stale one is refused here
         * with the same copy the pay boundary uses, before anything is written.
         */
        $confirmed = $data['fingerprint'] ?? null;
        unset($data['fingerprint']);

        if ($confirmed !== null && ! hash_equals($checkoutDriver->fingerprint($session), $confirmed)) {
            throw ValidationException::withMessages([
                'fingerprint' => $this->paymentRejectionMessage(new PaymentConfirmationException('fingerprint_mismatch')),
            ]);
        }

        $checkoutDriver->storeBillingAddress($session, $data);

        /*
         * The billing address is a fingerprint input (spec 0010 §D), and the
         * pay() flow writes it via XHR moments before pinning the session, so
         * a JSON caller gets the post-write fingerprint to pin against; a
         * redirect would discard it and the stale one would fail the pay
         * boundary. An Inertia billing element still gets back().
         */
        if ($request->wantsJson()) {
            return response()->json([
                'fingerprint' => $checkoutDriver->fingerprint($session),
            ]);
        }

        return back();
    }

    /**
     * Create (or resume) a confirmable intent for the chosen payment method
     * and record it on the session. The method must be registered and its
     * gateway driver must opt into the CreatesPaymentIntents capability.
     */
    public function storePaymentIntent(Request $request, CheckoutSessionModel $session, PaymentMethodRegistry $methods): JsonResponse
    {
        $this->ensureOwnership($session);

        $data = $request->validate([
            'payment_method' => ['required', 'string'],
            'mode' => ['sometimes', 'string', 'in:hold'],
            'renew' => ['sometimes', 'boolean'],
        ]);

        $cart = Cart::query()->findOrFail((int) $session->cart_reference);

        $method = $this->resolveAvailableMethod($methods, $cart, $data['payment_method']);

        $gateway = Payments::driver($method->driver());
        $holdMode = ($data['mode'] ?? null) === 'hold';

        if ($holdMode && ! $gateway instanceof SupportsPaymentHolds) {
            throw ValidationException::withMessages([
                'payment_method' => 'The selected payment method cannot authorise a hold.',
            ]);
        }

        if (! $holdMode && ! $gateway instanceof CreatesPaymentIntents) {
            throw ValidationException::withMessages([
                'payment_method' => 'The selected payment method cannot create a payment intent.',
            ]);
        }

        // Re-authorisation (payment change, or a hold the rail could not
        // stretch): release the old hold FIRST, so the customer is never
        // double-held. A void the gateway cannot confirm aborts the renewal.
        if ($holdMode && ($data['renew'] ?? false) && $session->payment_intent_ref !== null && $gateway instanceof SupportsPaymentIntents) {
            try {
                $gateway->voidIntent($session->payment_intent_ref);
            } catch (\Throwable $e) {
                report($e);

                throw ValidationException::withMessages([
                    'payment_method' => 'Your previous authorisation could not be released. Try again in a moment.',
                ]);
            }
        }

        /*
         * Switching away from a confirmed hold to a standard intent (the
         * customer backed out of the wallet flow and picked the card tab):
         * void the old hold FIRST, since nothing that follows can reach it
         * once its reference is overwritten below: an unvoided hold would
         * otherwise ride out its ~7 day authorisation window unreachable on
         * the customer's card. The old hold belongs to whichever driver
         * actually authorised it, not necessarily the driver the customer is
         * switching to, so it is resolved from the session's own meta rather
         * than $gateway (the incoming method's driver). A void the gateway
         * cannot confirm aborts, mirroring the renew branch above, rather
         * than silently orphaning the authorisation.
         */
        if (! $holdMode && $session->isHoldMode() && $session->payment_intent_ref !== null) {
            $previousGateway = Payments::driver((string) ($session->meta['payment_method'] ?? ''));

            if ($previousGateway instanceof SupportsPaymentIntents) {
                try {
                    $previousGateway->voidIntent($session->payment_intent_ref);
                } catch (\Throwable $e) {
                    report($e);

                    throw ValidationException::withMessages([
                        'payment_method' => 'Your previous authorisation could not be released. Try again in a moment.',
                    ]);
                }
            }
        }

        try {
            $descriptor = $holdMode
                ? $gateway->createHold($cart->calculate())
                : $gateway->createIntent($cart->calculate());
        } catch (\Throwable $e) {
            // Gateway errors carry internals (Stripe's minimum-charge copy
            // links to its own docs); log the real one, say something human.
            report($e);

            throw ValidationException::withMessages([
                'payment_method' => 'Card payments are unavailable right now. Please try again in a moment.',
            ]);
        }

        // Cart-scoped gateways (Stripe) reuse the cart's open intent, so a
        // successor session for the same cart receives the reference a dead
        // session still points at. The reference is unique (reconciliation
        // resolves a session BY intent), so the dead holder relinquishes it
        // first. A PaymentProcessing holder is never robbed: that pin means
        // the reference may be mid-charge.
        CheckoutSessionModel::query()
            ->where('payment_intent_ref', $descriptor->reference)
            ->whereKeyNot($session->getKey())
            ->whereNotState('status', PaymentProcessing::class)
            ->update(['payment_intent_ref' => null]);

        // The intent reference + driver key are what reconciliation resolves
        // by (PaymentIntentGateway reads meta.payment_method).
        $session->payment_intent_ref = $descriptor->reference;

        $meta = array_merge((array) $session->meta, ['payment_method' => $method->driver()]);

        if ($holdMode) {
            $meta['payment_intent_mode'] = 'hold';
        } else {
            unset($meta['payment_intent_mode']);
        }

        $session->meta = $meta;

        try {
            $session->save();
        } catch (UniqueConstraintViolationException $e) {
            // A pinned session still owns this intent (or a concurrent request
            // won the race). Either way: not payable right now, and the raw
            // SQL never reaches the customer.
            report($e);

            throw ValidationException::withMessages([
                'payment_method' => 'Another payment attempt for this basket is still finishing. Wait a moment and try again.',
            ]);
        }

        return response()->json([
            'intent' => $descriptor->reference,
            'clientSecret' => $descriptor->clientSecret,
        ]);
    }

    /**
     * The pay boundary, routed by the method's capability and the amount
     * (spec 0002 §A).
     *
     * Asynchronous — a gateway must confirm: pin the session (Open →
     * PaymentProcessing, amounts + fingerprint frozen) against the state the
     * customer confirmed. Completion then arrives via the gateway's success
     * path or reconciliation, never this endpoint.
     *
     * Synchronous — nothing to confirm (an offline / pay-on-collection /
     * invoice-terms method, or a zero total whatever the method claims):
     * complete in place, Open → Completed. Completing here is idempotent, so a
     * double submit yields one order.
     */
    public function pay(Request $request, CheckoutSessionModel $session, CheckoutDriver $checkoutDriver, PaymentMethodRegistry $methods): JsonResponse
    {
        $this->ensureOwnership($session);

        $data = $request->validate([
            'fingerprint' => ['required', 'string'],
            'payment_method' => ['required', 'string'],
        ]);

        $cart = Cart::query()->findOrFail((int) $session->cart_reference);

        $method = $this->resolveAvailableMethod($methods, $cart, $data['payment_method']);

        if ($method instanceof GuardsPayment && ($blocker = $method->paymentBlocker($session, $cart)) !== null) {
            throw ValidationException::withMessages(['payment_method' => $blocker]);
        }

        // Live amount, not the session's pinned figure — the pin happens
        // inside assertReadyForPayment(), after this decision.
        $amountTotal = $checkoutDriver->snapshot($session)->amountTotal;

        /*
         * The gateway intent was created when the payment form mounted, and
         * the basket has usually changed since (shipping selection, address
         * dependent rates). Bring its amount in line with the total the
         * customer is looking at BEFORE the pin, so a fingerprint rejection
         * costs nothing and a confirmed charge is never the stale figure.
         *
         * A confirmed hold cannot be synced like an unconfirmed intent
         * (Task 3 unit note: `$amountTotal` is Lunar minor units, equal to
         * Stripe's for GBP): it stretches (incremental authorization) or it
         * does not, so hold-mode sessions branch to adjustHold() instead of
         * SyncsPaymentIntents::syncIntent().
         */
        if ($method->requiresIntent() && $amountTotal > 0) {
            $gateway = Payments::driver($method->driver());

            if ($session->isHoldMode() && $gateway instanceof SupportsPaymentHolds && $session->payment_intent_ref !== null) {
                $adjustment = $gateway->adjustHold($session->payment_intent_ref, $amountTotal);

                if ($adjustment === HoldAdjustment::NeedsReauthorization) {
                    // Session still Open: nothing to unpin. The client
                    // re-opens the wallet for a fresh hold (payment-intent
                    // renew).
                    throw ValidationException::withMessages([
                        'hold' => 'hold_reauthorization_required',
                    ]);
                }
            } elseif ($gateway instanceof SyncsPaymentIntents) {
                $gateway->syncIntent(
                    Cart::query()->findOrFail((int) $session->cart_reference)->calculate(),
                );
            }
        }

        // Which method the customer chose, for the order stamp at completion
        // (spec 0014 §D). Distinct from meta.payment_method, which is the
        // gateway driver the intent code resolves. Written here, after the
        // guard and the hold/sync adjustment above have both already had the
        // chance to reject the attempt (a blocked guard, a reauthorization
        // requirement, an unvoidable previous hold), so a rejected attempt
        // never leaves a handle behind on the session.
        $session->forceFill([
            'meta' => array_merge((array) $session->meta, ['payment_handle' => $method->handle()]),
        ])->save();

        try {
            if (! $method->requiresIntent() || $amountTotal <= 0) {
                $completed = $checkoutDriver->complete($session, $data['fingerprint']);

                /*
                 * A hold-mode session can still reach this synchronous branch
                 * (a 100% discount applied on the squeeze page drops the
                 * total to zero): there is nothing left to charge, but the
                 * wallet's authorisation is still live on the gateway. Void
                 * it best-effort so it doesn't ride out its authorisation
                 * window dangling on a session that is already Completed.
                 * Best-effort only: a customer who owes nothing must not be
                 * blocked by a gateway hiccup on their way to a free order,
                 * and an operator can still void the hold from the gateway
                 * dashboard if this fails.
                 */
                if ($session->isHoldMode() && $session->payment_intent_ref !== null) {
                    $holdGateway = Payments::driver((string) ($session->meta['payment_method'] ?? ''));

                    if ($holdGateway instanceof SupportsPaymentIntents) {
                        try {
                            $holdGateway->voidIntent($session->payment_intent_ref);
                        } catch (\Throwable $e) {
                            report($e);
                        }
                    }
                }

                return response()->json([
                    'completed' => true,
                    'order' => $completed instanceof Order
                        ? (string) $completed->id
                        : (string) $completed,
                ]);
            }

            $checkoutDriver->assertReadyForPayment($session, $data['fingerprint']);
        } catch (PaymentConfirmationException $e) {
            // A stale fingerprint is routine customer behaviour; every other
            // reason (unorderable cart, diverged context) is a state worth a
            // log line, or the only trace is the generic copy on screen.
            if ($e->reason !== 'fingerprint_mismatch') {
                report($e);
            }

            throw ValidationException::withMessages(['fingerprint' => $this->paymentRejectionMessage($e)]);
        }

        return response()->json([
            'pinned' => true,
            // A hold session needs no gateway confirmation step client-side:
            // the client navigates straight to the poll, which captures at
            // reconcile.
            'processing' => $session->isHoldMode()
                ? route('lunar.checkout.processing', $session->uuid)
                : null,
        ]);
    }

    /**
     * Customer-initiated unpin (the client's gateway confirmation failed or
     * was abandoned). The action reopens the session only once the gateway
     * confirms no money was captured; a captured intent completes or refunds
     * instead, and an unconfirmable outcome stays frozen for reconciliation.
     */
    public function releasePayment(CheckoutSessionModel $session, ReconcilesCheckoutSession $reconcileCheckoutSession, CheckoutDriver $checkoutDriver): JsonResponse
    {
        $this->ensureOwnership($session);

        $outcome = $reconcileCheckoutSession->release($session);

        return response()->json([
            'outcome' => $outcome,
            'released' => in_array($outcome, ['released', 'refunded'], true),
            // The retry pins against the live fingerprint, so hand it over.
            'fingerprint' => $checkoutDriver->fingerprint($session),
        ]);
    }

    /**
     * The post-confirmation landing (spec 0010 §F, customer-facing half). The
     * client redirects here the moment the gateway accepts the confirmation;
     * this endpoint settles the session against the gateway's ACTUAL outcome
     * rather than trusting the client or waiting on the webhook: captured
     * completes and forwards to the store's success URL, a failed charge
     * reopens the session and sends the customer back to retry, and an
     * outcome still in flight renders a polling page (each poll lands back
     * here). The webhook remains first-class; everything here is idempotent
     * against it.
     */
    public function processing(CheckoutSessionModel $session, ReconcilesCheckoutSession $reconcileCheckoutSession, CheckoutTheme $theme): Response|RedirectResponse
    {
        $this->ensureOwnership($session);

        if ($session->status instanceof PaymentProcessing) {
            try {
                $reconcileCheckoutSession->execute($session);
                $session->refresh();
            } catch (\Throwable $e) {
                // Unknowable outcome: keep polling; the sweep owns the tail,
                // but the failure must not vanish.
                report($e);
            }
        }

        if ($session->status instanceof Completed) {
            return $this->redirectToSuccess($session);
        }

        /*
         * Reopened: the gateway says no money was captured. A hold-mode
         * session whose hold is still live and gateway-verified (the
         * manual-capture failure path that keeps the hold intact rather than
         * voiding it) belongs on the confirm ("squeeze") page, not the
         * standard checkout: landing on show() is the on-ramp to orphaning
         * that hold if the customer picks the card tab there instead (the
         * standard-intent branch of storePaymentIntent() now voids it, but
         * there's no reason to route the customer past that hazard at all
         * when the hold can simply be re-confirmed). Every other reopened
         * session (no hold, or one that no longer verifies) falls through to
         * the ordinary retry page. `?payment=failed` is read off
         * window.location.search by useCheckout.js regardless of which page
         * it lands on, so the error state still renders either way.
         */
        if ($session->status instanceof Open && ! $session->isExpired()) {
            $retryUrl = $this->liveHold($session) !== null
                ? route('lunar.checkout.confirm', $session->uuid)
                : route('lunar.checkout.show', $session->uuid);

            return redirect()->to($retryUrl.'?payment=failed');
        }

        if ($session->isExpired() || $session->status instanceof Cancelled) {
            return $this->redirectDeadSession($session);
        }

        Inertia::setRootView('lunar-checkout::app');

        return Inertia::render('Processing', [
            'pollUrl' => route('lunar.checkout.processing', $session->uuid),
            'merchant' => config('app.name', 'Store'),
            'theme' => $theme->tokens(),
            'branding' => $theme->branding(),
            'stylesheet' => $theme->stylesheet(),
            'favicon' => $theme->favicon(),
        ]);
    }

    /**
     * Session-level URL (hosted flow) wins, then the store-wide config
     * default, then home.
     */
    /**
     * Leave the checkout for the store's success page, handing over enough to
     * render an order confirmation: session()->put, NOT a redirect flash - the
     * processing page probes this redirect with a fetch before the browser
     * actually navigates, and a flash would be consumed by that probe. The key
     * persists until the next completed checkout overwrites it, so the
     * confirmation page survives a refresh.
     */
    private function redirectToSuccess(CheckoutSessionModel $session): RedirectResponse
    {
        if ($session->order_reference !== null) {
            session()->put('lunar.checkout.completed', [
                'uuid' => $session->uuid,
                'order_reference' => $session->order_reference,
            ]);
        }

        return redirect()->to($this->successUrl($session));
    }

    private function successUrl(CheckoutSessionModel $session): string
    {
        return $session->success_url ?: (config('lunar.checkout.urls.success') ?: '/');
    }

    private function cancelUrl(CheckoutSessionModel $session): string
    {
        return $session->cancel_url ?: (config('lunar.checkout.urls.cancel') ?: '/');
    }

    /**
     * The structured error contract for redirects OUT of the checkout. The
     * storefront reads the `lunar.checkout.error` session flash and decides
     * how to surface it: `code` is a stable machine code, `reason` is
     * customer-ready copy, `action` is what a storefront can offer next
     * (`view_basket`, `restart_checkout`).
     *
     * @return array{code: string, reason: string, action: string}
     */
    private function checkoutError(string $code, string $reason, string $action): array
    {
        return ['code' => $code, 'reason' => $reason, 'action' => $action];
    }

    /**
     * The one exit for a session that can no longer render: back to the
     * store's cancel URL with the reason attached.
     */
    private function redirectDeadSession(CheckoutSessionModel $session): RedirectResponse
    {
        $expired = $session->isExpired();

        return redirect()->to($this->cancelUrl($session))->with('lunar.checkout.error', $this->checkoutError(
            $expired ? 'session_expired' : 'session_cancelled',
            $expired
                ? 'Your checkout session expired, so we brought you back. Your basket is untouched.'
                : 'That checkout is no longer active. Your basket is untouched.',
            'restart_checkout',
        ));
    }

    /**
     * Customer-facing copy for a rejected pay attempt. The exception message
     * carries the developer reason code ("Payment confirmation rejected
     * [fingerprint_mismatch]."), which is for logs, not for the customer.
     */
    private function paymentRejectionMessage(PaymentConfirmationException $e): string
    {
        return match ($e->reason) {
            'fingerprint_mismatch' => 'Your order changed while you were checking out. Check the details above and try again.',
            'cart_not_orderable' => 'Your order cannot be placed right now. Check the details above and try again.',
            'pickup_point_required' => 'Choose where you would like to collect your order, then try again.',
            default => 'The payment could not be started. Refresh the page and try again.',
        };
    }

    /**
     * The registered method for this handle, provided the basket can actually
     * use it right now. "Available" means a survivor of
     * PaymentMethodRegistry::availableFor($cart), not merely
     * $method->isAvailable($cart) in isolation: that registry call is also
     * where the ExclusivePaymentMethod filter lives (spec 0014 §B), so a
     * handle that is individually available but excluded by a qualifying
     * exclusive method (e.g. card while on-account is offered) is rejected
     * here exactly as it is hidden from the projection, rather than quietly
     * honoured because the caller bypassed the UI.
     */
    private function resolveAvailableMethod(PaymentMethodRegistry $methods, Cart $cart, string $handle): PaymentMethod
    {
        $method = collect($methods->availableFor($cart))->first(
            fn (PaymentMethod $method): bool => $method->handle() === $handle,
        );

        if ($method === null) {
            throw ValidationException::withMessages([
                'payment_method' => 'The selected payment method is not available.',
            ]);
        }

        return $method;
    }

    /**
     * Project every registered element to the shape the frontend registry
     * renders. Each element is given the session, hydrated read-only, then
     * serialized to its handle, title, component hint, region, props and the
     * data already captured — plus the URL its component posts updates to.
     *
     * @return array<int, array<string, mixed>>
     */
    private function projectElements(CheckoutSessionModel $session): array
    {
        return array_map(function (CheckoutElement $element) use ($session): array {
            $element->setDataStore(new ModelElementStore($session));
            $element->mount();

            $props = $element->props();

            if ($element->handle() === 'contact') {
                // Guest round-trip: the element projects the auth user's email
                // (null for guests), so fall back to the email already persisted
                // on the session model — persistence bypasses the element bag.
                $props['email'] ??= $session->customer_email;
                $props['lookupUrl'] = route('lunar.checkout.contact.lookup', $session->uuid);
                $props['contactUrl'] = route('lunar.checkout.contact.store', $session->uuid);
            }

            return [
                'handle' => $element->handle(),
                'title' => $element->title(),
                'component' => $element->component(),
                'region' => $element->region(),
                'props' => $props,
                'data' => $element->data(),
                'storeUrl' => route('lunar.checkout.elements.store', [$session->uuid, $element->handle()]),
            ];
        }, $this->registry->all());
    }
}
