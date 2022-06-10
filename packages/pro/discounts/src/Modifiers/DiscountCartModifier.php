<?php

namespace GetCandy\Discounts\Modifiers;

use Closure;
use GetCandy\Base\CartLineModifier;
use GetCandy\Base\CartModifier;
use GetCandy\DataTypes\Price;
use GetCandy\Discounts\Models\Discount;
use GetCandy\Models\Cart;
use GetCandy\Models\CartLine;
use Illuminate\Support\Collection;

class DiscountCartModifier extends CartModifier
{
    public function calculatedLines(Collection $lines, Closure $next): Collection
    {
        clock()->event('Discounts')->color('purple')->begin();

        dd($lines);

//         $discounts = Discount::with([
//             'conditions.purchasables',
//             'rewards',
//         ])->get();
//
//         // $cartLine->discountTotal = new Price(
//         //     10,
//         //     $cartLine->cart->currency,
//         //     1
//         // );
//
//
//         // dd($discounts->toArray());
//
//         foreach ($discounts as $discount) {
//             foreach ($discount->conditions as $condition) {
//                 if ($condition->driver()->check($cartLine->cart)) {
//                     foreach ($discount->rewards as $reward) {
//                         $reward->driver()->apply($cartLine);
//                     }
//                 }
//                 continue;
//             }
//         }
//
//         clock()->event('Discounts')->end();

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
