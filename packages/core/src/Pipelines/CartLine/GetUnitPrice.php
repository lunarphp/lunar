<?php

namespace Lunar\Core\Pipelines\CartLine;

use Closure;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Facades\Pricing;
use Lunar\Core\Models\CartLine;
use Spatie\LaravelBlink\BlinkFacade as Blink;

class GetUnitPrice
{
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

        if ($customer = $cart->customer) {
            $customerGroups = $customer->customerGroups;
        } else {
            $cart->user?->loadMissing('customers.customerGroups');
            $customerGroups = $cart->user?->customers->pluck('customerGroups')->flatten();
        }

        $currency = Blink::once('currency_'.$cart->currency_id, function () use ($cart) {
            return $cart->currency;
        });

        $priceResponse = Pricing::currency($currency)
            ->qty($cartLine->quantity)
            ->currency($cart->currency)
            ->customerGroups($customerGroups)
            ->for($purchasable)
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
