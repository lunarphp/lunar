<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\DataTypes\Price;
use Lunar\Facades\Taxes;
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

        foreach ($cart->lines as $cartLine) {
            $subTotal = $cartLine->subTotal?->value;

            $unitQuantity = $cartLine->purchasable->getUnitQuantity();

            if (! is_null($cartLine->subTotalDiscounted?->value)) {
                $subTotal = $cartLine->subTotalDiscounted?->value;
            }

            $taxBreakDownResult = Taxes::setShippingAddress($cart->shippingAddress)
                ->setBillingAddress($cart->billingAddress)
                ->setCurrency($cart->currency)
                ->setPurchasable($cartLine->purchasable)
                ->setCartLine($cartLine)
                ->getBreakdown($subTotal);

            $taxBreakDownAmounts = $taxBreakDownAmounts->merge(
                $taxBreakDownResult->amounts
            );

            $taxTotal = $taxBreakDownResult->amounts->sum('price.value');

            $cartLine->taxBreakdown = $taxBreakDownResult;

            $cart->taxTotal = new Price($taxTotal, $cart->currency, 1);
            $cartLine->taxAmount = new Price($taxTotal, $cart->currency, $unitQuantity);
            $cartLine->total = new Price($subTotal + $taxTotal, $cart->currency, $unitQuantity);
        }

        $taxBreakDown = new TaxBreakdown($taxBreakDownAmounts);

        $taxTotal = $cart->lines->sum('taxAmount.value');
        $taxBreakDownAmounts = $taxBreakDown->amounts->filter()->flatten();

        if ($shippingAddress = $cart->shippingAddress) {

            $taxTotal += $shippingAddress->shippingTaxTotal?->value;
            $shippingTaxBreakdown = $shippingAddress->taxBreakdown;

            if ($shippingTaxBreakdown) {
                $taxBreakDownAmounts = $taxBreakDownAmounts->merge(
                    $shippingTaxBreakdown->amounts
                );
            }
        }

        $cart->taxTotal = new Price($taxTotal, $cart->currency, 1);

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
