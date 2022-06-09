<?php

namespace GetCandy\Discounts\Modifiers;

use Closure;
use GetCandy\Base\CartLineModifier;
use GetCandy\Base\CartModifier;
use GetCandy\Discounts\Models\Discount;
use GetCandy\Models\Cart;
use GetCandy\Models\CartLine;

class DiscountCartModifier extends CartLineModifier
{
    public function calculating(CartLine $cartLine, Closure $next): CartLine
    {
        clock()->event('Discounts')->color('purple')->begin();

        $discounts = Discount::with([
            'conditions.purchasables',
            'rewards',
        ])->get();


        // dd($discounts->toArray());

        foreach ($discounts as $discount) {
            foreach ($discount->conditions as $condition) {
                // dd($condition->purchasables);
                // if ($condition->driver()->check($cart)) {

                    foreach ($discount->rewards as $reward) {
                        $reward->driver()->check($cartLine->cart);
                    }

                // }

                continue;
            }

        }

        clock()->event('Discounts')->end();

        return $next($cartLine);
    }
//
//     public function calculated(Cart $cart, Closure $next): Cart
//     {
//         // dd(2);
//
//         return $next($cart);
//     }
}
