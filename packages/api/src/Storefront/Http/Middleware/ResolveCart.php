<?php

namespace Lunar\Api\Storefront\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Lunar\Api\Contracts\CartTokenCodec;
use Lunar\Api\Http\Exceptions\ApiException;
use Lunar\Core\Contracts\CartSession;
use Lunar\Core\Managers\CartSessionManager;
use Lunar\Core\Models\Cart;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies the `X-Lunar-Cart` token, loads the cart and hands it to
 * `CartSession::use()` so every downstream `CartSession::current()` call works
 * unchanged. Whatever cart is current after the request is returned as a
 * fresh token on the response.
 */
class ResolveCart
{
    public function __construct(
        protected CartTokenCodec $codec,
        protected CartSession $cartSession,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($token = $request->header('X-Lunar-Cart')) {
            $publicId = $this->codec->decode((string) $token)
                ?? throw ApiException::make(401, 'invalid_cart_token', [], ['header' => 'X-Lunar-Cart']);

            $cart = Cart::query()->wherePublicId($publicId)->whereNull('completed_at')->first()
                ?? throw ApiException::make(404, 'cart_not_found', [], ['header' => 'X-Lunar-Cart']);

            $this->cartSession->use($cart);
        }

        $response = $next($request);

        if ($cart = $this->currentCart()) {
            $response->headers->set('X-Lunar-Cart', $this->codec->encode($cart));
        }

        return $response;
    }

    /** The cart in the session without forcing a calculation to find out. */
    protected function currentCart(): ?Cart
    {
        return $this->cartSession instanceof CartSessionManager
            ? $this->cartSession->current(calculate: false)
            : $this->cartSession->current();
    }
}
