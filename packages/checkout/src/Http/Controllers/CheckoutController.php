<?php

namespace Lunar\Checkout\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Contracts\CheckoutElement;
use Lunar\Checkout\Contracts\CheckoutSession;
use Lunar\Checkout\Contracts\ElementRegistry;
use Lunar\Checkout\Contracts\PaymentMethod;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Checkout\DataObjects\CheckoutTheme;
use Lunar\Checkout\Exceptions\PaymentConfirmationException;
use Lunar\Checkout\Models\CheckoutSession as CheckoutSessionModel;
use Lunar\Checkout\States\CheckoutSession\Cancelled;
use Lunar\Checkout\States\CheckoutSession\Completed;
use Lunar\Core\Contracts\CreatesPaymentIntents;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Order;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CheckoutController extends Controller
{
    /**
     * Collaborators are constructor-injected per the service-layer DI rule
     * (spec 0016): the element registry and the checkout session are bound to
     * their contracts in the service provider.
     */
    public function __construct(
        private readonly ElementRegistry $registry,
        private readonly CheckoutSession $session,
    ) {}

    /**
     * Start (or resume) a checkout for the current cart and redirect to its
     * UUID URL. Session creation is a POST-only mutation — never a side effect
     * of loading the cart or hitting a bare GET (spec 0004 §F). `resolveOrCreate`
     * resumes the cart's live `Open` session, so re-pressing "Checkout" lands
     * back on the same session rather than churning a new one.
     */
    public function start(CheckoutDriver $checkoutDriver): SymfonyResponse
    {
        $cart = CartSession::current();

        // No cart to check out — bounce back to wherever the request came from
        // (the cart page), never a hard 500. Empty-lines gating is a UI concern
        // (the storefront only shows the CTA for a non-empty cart); a zero-total
        // cart is itself a valid, driver-handled case (spec 0010).
        if ($cart === null) {
            return redirect()->back()->with('checkout_error', 'Your basket is empty.');
        }

        $session = $checkoutDriver->resolveOrCreateSession($cart);

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

        // A login mid-checkout can merge/swap the live cart (auth_policy=merge).
        // cart_reference is pinned identity, so rather than mutate it, re-resolve
        // to the surviving cart's session and move the customer there. Guarded to
        // signed-in owners; a guest with a different cart already failed ownership.
        if ($user = auth()->user()) {
            $cart = CartSession::current();

            if ($cart !== null && (string) $cart->id !== $session->cart_reference) {
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

        // Order already placed — send to the stored return URL if the caller
        // set one (hosted flow), otherwise home. TODO(payment): the storefront
        // return routes (checkout.success / order-issue) land with the payment
        // section; wire them here then.
        if ($session->status instanceof Completed) {
            return redirect()->to($session->success_url ?: '/');
        }

        // A dead capability token (expired window / cancelled) can't render a
        // live checkout. Bounce home; the customer restarts from the cart.
        if ($session->isExpired() || $session->status instanceof Cancelled) {
            return redirect()->to($session->cancel_url ?: '/')->with('checkout_error', 'Your checkout session has expired.');
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
        ]);
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

        return [
            'uuid' => $session->uuid,
            'merchant' => config('checkout.merchant') ?: config('app.name'),
            'currency' => $snapshot->currencyCode,
            'items' => array_map(fn (array $line): array => [
                'id' => $line['identifier'],
                'title' => $line['description'],
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
            'shippingAddress' => $driver->getShippingAddress($session),
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
                'supportsExpress' => $method->supportsExpress(),
                'expressComponent' => $method->expressComponent(),
            ], app(PaymentMethodRegistry::class)->all()),
            'urls' => [
                'shippingAddress' => route('lunar.checkout.shipping-address.store', $session->uuid),
                'billingAddress' => route('lunar.checkout.billing-address.store', $session->uuid),
                'shippingOption' => route('lunar.checkout.shipping-option.store', $session->uuid),
                'paymentIntent' => route('lunar.checkout.payment-intent.store', $session->uuid),
                'pay' => route('lunar.checkout.pay', $session->uuid),
            ],
        ];
    }

    /**
     * Validate and persist one element's captured data into the checkout
     * session. The element owns its rules and its write path (the prototype
     * writes to the session; the spec 0001 model writes through the cart API).
     */
    public function storeElement(Request $request, string $handle): RedirectResponse
    {
        $element = $this->registry->get($handle);

        abort_if($element === null, 404);

        $element->setSession($this->session);

        $validated = $request->validate($element->rules());

        $element->store($validated);

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

        $data = $request->validate($this->addressRules());

        $checkoutDriver->storeShippingAddress($session, $data);

        return back();
    }

    /**
     * The backend-neutral address payload (spec 0010 §B), shared by the
     * shipping and billing stores.
     *
     * @return array<string, array<int, mixed>>
     */
    private function addressRules(): array
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
            'country_code' => ['required', 'string', Rule::exists(Country::class, 'iso2')],
            'phone' => ['nullable', 'string', 'max:32'],
        ];
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
     * Store the billing address through the driver — same neutral payload as
     * the shipping address; the frontend defaults it to the delivery address.
     */
    public function storeBillingAddress(Request $request, CheckoutSessionModel $session, CheckoutDriver $checkoutDriver): RedirectResponse
    {
        $this->ensureOwnership($session);

        $data = $request->validate($this->addressRules());

        $checkoutDriver->storeBillingAddress($session, $data);

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

        $data = $request->validate(['payment_method' => ['required', 'string']]);

        $method = $methods->get($data['payment_method']);

        if ($method === null) {
            throw ValidationException::withMessages([
                'payment_method' => 'The selected payment method is not available.',
            ]);
        }

        $gateway = Payments::driver($method->driver());

        if (! $gateway instanceof CreatesPaymentIntents) {
            throw ValidationException::withMessages([
                'payment_method' => 'The selected payment method cannot create a payment intent.',
            ]);
        }

        $cart = Cart::query()->findOrFail((int) $session->cart_reference);

        $descriptor = $gateway->createIntent($cart->calculate());

        // The intent reference + driver key are what reconciliation resolves
        // by (PaymentIntentGateway reads meta.payment_method).
        $session->payment_intent_ref = $descriptor->reference;
        $session->meta = array_merge((array) $session->meta, ['payment_method' => $method->driver()]);
        $session->save();

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

        $method = $methods->get($data['payment_method']);

        if ($method === null) {
            throw ValidationException::withMessages([
                'payment_method' => 'The selected payment method is not available.',
            ]);
        }

        // Live amount, not the session's pinned figure — the pin happens
        // inside assertReadyForPayment(), after this decision.
        $amountTotal = $checkoutDriver->snapshot($session)->amountTotal;

        try {
            if (! $method->requiresIntent() || $amountTotal <= 0) {
                $completed = $checkoutDriver->complete($session, $data['fingerprint']);

                return response()->json([
                    'completed' => true,
                    'order' => $completed instanceof Order
                        ? (string) $completed->id
                        : (string) $completed,
                ]);
            }

            $checkoutDriver->assertReadyForPayment($session, $data['fingerprint']);
        } catch (PaymentConfirmationException $e) {
            throw ValidationException::withMessages(['fingerprint' => $e->getMessage()]);
        }

        return response()->json(['pinned' => true]);
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
            $element->setSession($this->session);
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
                'storeUrl' => route('lunar.checkout.elements.store', $element->handle()),
            ];
        }, $this->registry->all());
    }
}
