<?php

namespace Lunar\Pipelines\CartLine;

use Closure;
use Lunar\DataTypes\Price;
use Lunar\Facades\Pricing;
use Lunar\Models\CartLine;
use Spatie\LaravelBlink\BlinkFacade as Blink;

class GetUnitPrice
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function handle(CartLine $cartLine, Closure $next)
    {
        $purchasable = $cartLine->purchasable;
        $cart = $cartLine->cart;

        $customerGroups = $cart->user?->customers->pluck('customerGroups')->flatten();

        $currency = Blink::once('currency_'.$cart->currency_id, function () use ($cart) {
           return $cart->currency;
        });

        $priceResponse = Pricing::currency($currency)
            ->qty($cartLine->quantity)
            ->currency($cart->currency)
            ->customerGroups($customerGroups)
            ->for($purchasable)
            ->get();

        $cartLine->unitPrice = new Price(
            $priceResponse->matched->price->value,
            $cart->currency,
            $purchasable->getUnitQuantity()
        );

        return $next($cartLine);
    }
}
