<?php

namespace Lunar\Core\Pipelines\Cart;

use Closure;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Facades\Taxes;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Contracts\Cart as CartContract;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;
use Lunar\Core\ValueObjects\Cart\TaxBreakdownAmount;

class CalculateTax
{
    /**
     * Called just before cart totals are calculated.
     *
     * @param  Closure(CartContract): mixed  $next
     */
    public function handle(CartContract $cart, Closure $next): mixed
    {
        /** @var Cart $cart */
        $taxBreakDownAmounts = collect();

        foreach ($cart->lines as $cartLine) {
            $subTotal = $cartLine->subTotal?->value;

            if (! is_null($cartLine->subTotalDiscounted?->value)) {
                $subTotal = $cartLine->subTotalDiscounted?->value;
            }

            $taxBreakDownResult = Taxes::setShippingAddress($cart->shippingAddress)
                ->setBillingAddress($cart->billingAddress)
                ->setCurrency($cart->currency)
                ->setPurchasable($cartLine->purchasable)
                ->setCartLine($cartLine)
                ->setTaxZone($cart->taxZone)
                ->getBreakdown($subTotal);

            $taxBreakDownAmounts = $taxBreakDownAmounts->merge(
                $taxBreakDownResult->amounts
            );

            $taxTotal = $taxBreakDownResult->amounts->sum('price.value');

            $cartLine->taxBreakdown = $taxBreakDownResult;

            $cart->taxTotal = new PriceValue($taxTotal, $cart->currency);
            $cartLine->taxAmount = new PriceValue($taxTotal, $cart->currency);

            if (prices_inc_tax()) {
                $cartLine->total = new PriceValue($subTotal, $cart->currency);
            } else {
                $cartLine->total = new PriceValue($subTotal + $taxTotal, $cart->currency);
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
                ->setTaxZone($cart->taxZone)
                ->getBreakdown($shippingSubTotal);

            $shippingTaxTotal = new PriceValue($shippingTax->amounts->sum('price.value'), $cart->currency);

            $cart->shippingTaxTotal = $shippingTaxTotal;
            $taxTotal += $shippingTaxTotal->value;

            $taxBreakDownAmounts = $taxBreakDownAmounts->merge(
                $shippingTax->amounts
            );

            $shippingTotalValue = $shippingSubTotal;
            if (! prices_inc_tax()) {
                $shippingTotalValue += $shippingTaxTotal->value;
            }

            $shippingTotal = new PriceValue($shippingTotalValue, $cart->currency);

            $cart->shippingTotal = $shippingTotal;

            if ($cart->shippingAddress && ! $cart->shippingOptionOverride) {
                $cart->shippingAddress->taxBreakdown = $shippingTax;
                $cart->shippingAddress->shippingTaxTotal = $shippingTaxTotal;
                $cart->shippingAddress->shippingTotal = $shippingTotal;
            }
        }

        $cart->taxTotal = new PriceValue($taxTotal, $cart->currency);

        // Need to include shipping tax breakdown...
        $cart->taxBreakdown = new TaxBreakdown(
            $taxBreakDownAmounts->groupBy('identifier')->map(function ($amounts) use ($cart) {
                return new TaxBreakdownAmount(
                    price: new PriceValue($amounts->sum('price.value'), $cart->currency),
                    percentage: $amounts->first()->percentage,
                    description: $amounts->first()->description,
                    identifier: $amounts->first()->identifier
                );
            })
        );

        return $next($cart);
    }
}
