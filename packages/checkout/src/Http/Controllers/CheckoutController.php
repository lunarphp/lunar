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
use Lunar\Core\Facades\CartSession;

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
     * Render the checkout against the package's OWN Inertia root view — the
     * checkout is a self-contained app, not a guest in the consumer's Inertia
     * setup (spec 0008 §A). Data arrives as no-store props, so no PII is baked
     * into cacheable HTML.
     *
     * The current cart is ingested into its live `Open` checkout session
     * (resolve-or-create, so a refresh resumes rather than churns), then the
     * session is projected into the shape the Vue app consumes. Registered
     * custom elements (§ registry) are projected alongside it.
     */
    public function show(CheckoutDriver $checkoutDriver, CheckoutTheme $theme): Response
    {
        $session = $checkoutDriver->resolveOrCreateSession(
            CartSession::current()
        );

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
