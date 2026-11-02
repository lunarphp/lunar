<?php

namespace Lunar\Core\Pipelines\CartLine;

use Closure;
use Lunar\Core\Contracts\PricingManager;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\CartLine;

class GetUnitPrice
{
    public function __construct(
        protected PricingManager $pricing,
    ) {}

    /**
     * Called just before cart totals are calculated.
     *
     * @param  Closure(CartLine):mixed  $next
     * @return Closure
     */
    public function handle(CartLine $cartLine, Closure $next)
    {
        /** @var CartLine $cart */
        $purchasable = $cartLine->purchasable;
        $cart = $cartLine->cart;

        $priceResponse = $this->pricing
            ->using($cart->context())
            ->for($purchasable)
            ->qty($cartLine->quantity)
            ->get();

        $cartLine->unitPrice = new PriceValue(
            (int) $priceResponse->matched->price,
            $cart->currency,
        );

        $cartLine->unitPriceInclTax = new PriceValue(
            $priceResponse->matched->priceIncTax($cart->taxZone)->value,
            $cart->currency,
        );

        return $next($cartLine);
    }
}
