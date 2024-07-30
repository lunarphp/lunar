<?php

namespace Lunar\Paypal\Http\Controllers;

use Illuminate\Routing\Controller;
use Lunar\Facades\CartSession;
use Lunar\Paypal\Facades\Paypal;

class GetPaypalOrderController extends Controller
{
    public function __invoke()
    {
        $cart = CartSession::current();

        return response()->json(
            Paypal::buildInitialOrder($cart)
        );
    }
}
