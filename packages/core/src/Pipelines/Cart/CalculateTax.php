<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Base\ValueObjects\Cart\TaxBreakdownAmount;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;

class CalculateTax
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function handle(Cart $cart, Closure $next)
    {
        $taxBreakDownAmounts = collect();

        $taxBreakDown = new TaxBreakdown($taxBreakDownAmounts);

        $taxTotal = $cart->lines->sum('taxAmount.value');
        $taxBreakDownAmounts = $taxBreakDown->amounts->filter()->flatten();

        $cart->taxTotal = new Price($taxTotal, $cart->currency, 1);

        // Need to include shipping tax breakdown...
        $cart->taxBreakdown = new TaxBreakdown(
            $taxBreakDownAmounts->groupBy('identifier')->map(function ($amounts) use ($cart) {
                return new TaxBreakdownAmount(
                    price: new Price($amounts->sum('price.value'), $cart->currency, 1),
                    identifier: $amounts->first()->identifier,
                    description: $amounts->first()->description,
                    percentage: $amounts->first()->percentage
                );
            })
        );

        return $next($cart);
    }
}
