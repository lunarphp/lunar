<?php

namespace Lunar\Api\Storefront\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Api\Http\Responses\Envelope;
use Lunar\Api\OpenApi\Attributes\Operation;
use Lunar\Api\OpenApi\Attributes\Responds;
use Lunar\Api\Storefront\Resources\V1\CartResource;

class CartController extends Controller
{
    protected string $resource = CartResource::class;

    /** The current cart, or `data: null` when the request carries none. */
    #[Operation(summary: 'Retrieve the current cart', description: 'Returns the cart identified by the X-Lunar-Cart header, calculated for the request context. `data` is null when the request carries no cart token.')]
    #[Responds(CartResource::class, description: 'The current cart, or `data: null` without a cart token.')]
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
