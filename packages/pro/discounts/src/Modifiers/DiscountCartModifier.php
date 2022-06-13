<?php

namespace GetCandy\Discounts\Modifiers;

use Closure;
use GetCandy\Base\CartLineModifier;
use GetCandy\Base\CartModifier;
use GetCandy\DataTypes\Price;
use GetCandy\Discounts\Models\Discount;
use GetCandy\Models\Cart;
use GetCandy\Models\CartLine;

class DiscountCartModifier extends CartLineModifier
{
    public function subtotalled(CartLine $cartLine, Closure $next): CartLine
    {
        clock()->event('Discounts')->color('purple')->begin();

        $discounts = Discount::with([
            'conditions.purchasables',
            'rewards',
        ])->get();

        // $cartLine->discountTotal = new Price(
        //     10,
        //     $cartLine->cart->currency,
        //     1
        // );


        // dd($discounts->toArray());

        foreach ($discounts as $discount) {
            foreach ($discount->conditions as $condition) {
                if ($condition->driver()->check($cartLine->cart)) {
                    foreach ($discount->rewards as $reward) {
                        $cartLine = $reward->driver()->apply($cartLine);
                    }
                }
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
