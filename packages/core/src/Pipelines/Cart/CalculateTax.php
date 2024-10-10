<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Base\ValueObjects\Cart\TaxBreakdownAmount;
use Lunar\DataTypes\Price;
use Lunar\Facades\ShippingManifest;
use Lunar\Facades\Taxes;
use Lunar\Models\Cart;

class CalculateTax
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return mixed
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

            if (prices_inc_tax()) {
                $cartLine->total = new Price($subTotal, $cart->currency, $unitQuantity);
            } else {
                $cartLine->total = new Price($subTotal + $taxTotal, $cart->currency, $unitQuantity);
            }
        }

        $taxBreakDown = new TaxBreakdown($taxBreakDownAmounts);

        $taxTotal = $cart->lines->sum('taxAmount.value');
        $taxBreakDownAmounts = $taxBreakDown->amounts->filter()->flatten();

        $shippingOption = $cart->shippingOptionOverride ?: ShippingManifest::getShippingOption($cart);

        if ($shippingOption) {
            $shippingSubTotal = $cart->shippingBreakdown->items->sum('price.value');

            $shippingTax = Taxes::setShippingAddress($cart->shippingAddress)
                ->setCurrency($cart->currency)
                ->setPurchasable($shippingOption)
                ->getBreakdown($shippingSubTotal);

            $shippingTaxTotal = $shippingTax->amounts->sum('price.value');
            $shippingTaxTotal = new Price($shippingTaxTotal, $cart->currency, 1);

            $taxTotal += $shippingTaxTotal?->value;

            if ($cart->shippingAddress && ! $cart->shippingOptionOverride) {
                $cart->shippingAddress->taxBreakdown = $shippingTax;
                $cart->shippingAddress->shippingTaxTotal = $shippingTaxTotal;
            }

            $taxBreakDownAmounts = $taxBreakDownAmounts->merge(
                $shippingTax->amounts
            );

            $shippingTotal = $shippingSubTotal;
            if (! prices_inc_tax()) {
                $shippingTotal += $shippingTaxTotal?->value;
            }
            $cart->shippingTotal = new Price(
                $shippingTotal,
                $cart->currency,
                1
            );
        }

        $cart->taxTotal = new Price($taxTotal, $cart->currency, 1);

        // Need to include shipping tax breakdown...
        $cart->taxBreakdown = new TaxBreakdown(
            $taxBreakDownAmounts->groupBy('identifier')->map(function ($amounts) use ($cart) {
                return new TaxBreakdownAmount(
                    price: new Price($amounts->sum('price.value'), $cart->currency, 1),
                    percentage: $amounts->first()->percentage,
                    description: $amounts->first()->description,
                    identifier: $amounts->first()->identifier
                );
            })
        );

        return $next($cart);
    }
}
