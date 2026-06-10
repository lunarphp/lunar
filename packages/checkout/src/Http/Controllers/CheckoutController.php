<?php

namespace Lunar\Checkout\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Checkout\Contracts\CheckoutAssets;
use Lunar\Checkout\Contracts\CheckoutElement;
use Lunar\Checkout\Contracts\CheckoutSession;
use Lunar\Checkout\Contracts\ElementRegistry;
use Lunar\Checkout\DataObjects\CheckoutTheme;
use Lunar\Checkout\Support\CheckoutBundle;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
     * Until the element model + checkout session land (specs 0001/0004), the
     * order body is a static placeholder fixture; registered custom elements
     * (§ registry) are projected alongside it.
     */
    public function show(CheckoutTheme $theme): Response
    {
        Inertia::setRootView('lunar-checkout::app');

        return Inertia::render('Show', [
            'checkout' => array_merge($this->placeholderCheckout(), [
                'elements' => $this->projectElements(),
            ]),
            'theme' => $theme->tokens(),
        ]);
    }

    /**
     * Stream a file from the checkout app's own prebuilt dist/ (spec 0008 §B).
     * Same-origin, far-future immutable cache. Only files inside dist/ resolve.
     */
    public function build(string $file, CheckoutBundle $bundle): BinaryFileResponse
    {
        $path = $bundle->file($file);

        abort_if($path === null, 404);

        return $this->serve($path, $file);
    }

    /**
     * Stream a registered contributed chunk (spec 0009 §C.1). Only registered
     * package + filename pairs resolve — never an arbitrary request path.
     */
    public function asset(string $package, string $file, CheckoutAssets $assets): BinaryFileResponse
    {
        $path = $assets->path($package, $file);

        abort_if($path === null, 404);

        return $this->serve($path, $file);
    }

    private function serve(string $path, string $file): BinaryFileResponse
    {
        return response()->file($path, [
            'Content-Type' => str_ends_with($file, '.css') ? 'text/css' : 'text/javascript',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
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

    /**
     * Placeholder order payload mirroring the shape the components consume.
     * Prices are in minor units (pence). Replaced by the CheckoutData DTO
     * (spec 0001) once it exists.
     *
     * @return array<string, mixed>
     */
    private function placeholderCheckout(): array
    {
        return [
            'merchant' => 'Atelier Hudson',
            'currency' => 'GBP',
            'vatRate' => 0.20,
            'items' => [
                ['id' => 'knit', 'title' => 'Merino crew knit', 'variant' => 'Charcoal · M', 'sku' => 'AH-2241-CHM', 'qty' => 1, 'price' => 12800, 'icon' => 'shirt'],
                ['id' => 'oxford', 'title' => 'Cotton oxford shirt', 'variant' => 'White · M', 'sku' => 'AH-1180-WHM', 'qty' => 2, 'price' => 5800, 'icon' => 'shirt'],
                ['id' => 'card', 'title' => 'Leather card holder', 'variant' => 'Tan', 'sku' => 'AH-0563-TAN', 'qty' => 1, 'price' => 4500, 'icon' => 'wallet'],
                ['id' => 'coat', 'title' => 'Wool overcoat', 'variant' => 'Camel · L', 'sku' => 'AH-3390-CAM', 'qty' => 1, 'price' => 32000, 'icon' => 'shirt'],
                ['id' => 'watch', 'title' => 'Field watch', 'variant' => 'Stainless', 'sku' => 'AH-9001-STL', 'qty' => 1, 'price' => 18500, 'icon' => 'watch'],
                ['id' => 'scarf', 'title' => 'Lambswool scarf', 'variant' => 'Forest', 'sku' => 'AH-0712-FOR', 'qty' => 1, 'price' => 6500, 'icon' => 'shirt'],
            ],
            'shippingMethods' => [
                ['id' => 'standard', 'name' => 'Standard delivery', 'sub' => 'Free over £50 · 3–5 working days', 'price' => 0],
                ['id' => 'express', 'name' => 'Express delivery', 'sub' => 'Next working day', 'price' => 695],
                ['id' => 'nominated', 'name' => 'Nominated day', 'sub' => 'Choose a weekday', 'price' => 495],
            ],
            'validCodes' => [
                'TEST10' => ['type' => 'pct', 'value' => 10, 'label' => '10% off'],
                'FIXED10' => ['type' => 'fixed', 'value' => 1000, 'label' => '£10 off'],
                'FREESHIP' => ['type' => 'freeship', 'label' => 'Free shipping'],
            ],
        ];
    }
}
