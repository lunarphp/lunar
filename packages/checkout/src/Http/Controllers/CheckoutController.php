<?php

namespace Lunar\Checkout\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Checkout\DataObjects\CheckoutTheme;

class CheckoutController extends Controller
{
    /**
     * Render the checkout page as an Inertia response.
     *
     * The Vue components are transport-neutral: they take the `checkout`
     * payload and the resolved `theme` token map as props. Until the element
     * model + checkout session land (specs 0001/0004), `checkout` is a static
     * placeholder fixture so the page renders end-to-end ahead of the backend.
     */
    public function show(CheckoutTheme $theme): Response
    {
        return Inertia::render('Checkout', [
            'checkout' => $this->placeholderCheckout(),
            'theme' => $theme->tokens(),
        ]);
    }

    /**
     * Placeholder order payload mirroring the shape the components consume.
     * Replaced by the CheckoutData DTO (spec 0001) once it exists.
     *
     * @return array<string, mixed>
     */
    private function placeholderCheckout(): array
    {
        return [
            'merchant' => 'Wattson',
            'currency' => 'USD',
            'items' => [
                ['name' => 'Pixel 9 Pro', 'qty' => 1, 'price' => 99900, 'icon' => 'smartphone'],
                ['name' => 'USB-C cable', 'qty' => 2, 'price' => 1900, 'icon' => 'cable'],
            ],
            'shipping' => 0,
            'tax' => 8392,
            'discount' => ['code' => 'SAVE10', 'amount' => 10000, 'applied' => false],
        ];
    }
}
