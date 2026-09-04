<?php

namespace Lunar\Api\Storefront\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Api\Http\Responses\Envelope;
use Lunar\Api\Storefront\Resources\V1\CartResource;

class CartController extends Controller
{
    protected string $resource = CartResource::class;

    /** The current cart, or `data: null` when the request carries none. */
    public function current(Request $request): JsonResponse
    {
        $context = $this->context($request);
        $cart = $this->cartSession()->current();

        return Envelope::item(
            $cart ? $this->definition()->serialize($cart, $context) : null,
            $this->meta($request, $context),
            ['self' => $request->fullUrl()],
        );
    }
}
