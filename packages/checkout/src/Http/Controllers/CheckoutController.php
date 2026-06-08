<?php

namespace Lunar\Checkout\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class CheckoutController extends Controller
{
    /**
     * Render the checkout page.
     *
     * Barebones placeholder: this is the mount point a consuming storefront
     * renders the checkout from. The element model, session, and the
     * <LunarCheckout> render layer land in later checkout specs.
     */
    public function show(): View
    {
        return view('lunar-checkout::show');
    }
}
