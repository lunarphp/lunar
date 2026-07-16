<?php

namespace Lunar\Checkout\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Contracts\CheckoutElement;
use Lunar\Checkout\Contracts\CheckoutSession;
use Lunar\Checkout\Contracts\ElementRegistry;
use Lunar\Checkout\DataObjects\CheckoutTheme;
use Lunar\Checkout\Models\CheckoutSession as CheckoutSessionModel;
use Lunar\Checkout\States\CheckoutSession\Cancelled;
use Lunar\Checkout\States\CheckoutSession\Completed;
use Lunar\Core\Facades\CartSession;
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
     */
    public function show(CheckoutSessionModel $session, CheckoutDriver $checkoutDriver, CheckoutTheme $theme): Response|RedirectResponse
    {
        $this->ensureOwnership($session);

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
                ['elements' => $this->projectElements()],
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
            ], $driver->getShippingOptions($session)),
            'shippingId' => $driver->getSelectedShippingOption($session),
            'coupon' => $driver->getCoupon($session),
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
     * Project every registered element to the shape the frontend registry
     * renders. Each element is given the session, hydrated read-only, then
     * serialized to its handle, title, component hint, region, props and the
     * data already captured — plus the URL its component posts updates to.
     *
     * @return array<int, array<string, mixed>>
     */
    private function projectElements(): array
    {
        return array_map(function (CheckoutElement $element): array {
            $element->setSession($this->session);
            $element->mount();

            return [
                'handle' => $element->handle(),
                'title' => $element->title(),
                'component' => $element->component(),
                'region' => $element->region(),
                'props' => $element->props(),
                'data' => $element->data(),
                'storeUrl' => route('lunar.checkout.elements.store', $element->handle()),
            ];
        }, $this->registry->all());
    }
}
