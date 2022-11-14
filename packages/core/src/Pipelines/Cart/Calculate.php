<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;

class Calculate
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function handle(Cart $cart, Closure $next)
    {
        $subTotal = $cart->lines->sum('subTotal.value');
        $discountTotal = $cart->lines->sum('discountTotal.value') + $cart->cartDiscountAmount?->value;
        $taxTotal = $cart->lines->sum('taxAmount.value');
        $total = $cart->lines->sum('total.value') - $discountTotal;
        $taxBreakDownAmounts = $cart->lines->pluck('taxBreakdown')->pluck('amounts')->flatten();

        // Get the shipping address
        if ($shippingAddress = $cart->shippingAddress) {
            if ($shippingAddress->shippingSubTotal) {
                $subTotal += $shippingAddress->shippingSubTotal?->value;
                $total += $shippingAddress->shippingTotal?->value;
                $taxTotal += $shippingAddress->taxTotal?->value;
                $shippingTaxBreakdown = $shippingAddress->taxBreakdown;

                if ($shippingTaxBreakdown) {
                    $taxBreakDownAmounts = $taxBreakDownAmounts->merge(
                        $shippingTaxBreakdown->amounts
                    );
                }
            }
        }

        $cart->subTotal = new Price($subTotal, $cart->currency, 1);
        $cart->discountTotal = new Price($discountTotal, $cart->currency, 1);
        $cart->taxTotal = new Price($taxTotal, $cart->currency, 1);
        $cart->total = new Price($total, $cart->currency, 1);

        // Need to include shipping tax breakdown...
        $cart->taxBreakdown = $taxBreakDownAmounts->groupBy('identifier')->map(function ($amounts) use ($cart) {
            return [
                'percentage' => $amounts->first()->percentage,
                'description' => $amounts->first()->description,
                'identifier' => $amounts->first()->identifier,
                'amounts' => $amounts,
                'total' => new Price($amounts->sum('price.value'), $cart->currency, 1),
            ];
        });

        return $next($cart);
    }
}
