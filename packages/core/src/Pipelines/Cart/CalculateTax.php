<?php

namespace Lunar\Core\Pipelines\Cart;

use Closure;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Facades\Taxes;
use Lunar\Core\Models\Cart;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;
use Lunar\Core\ValueObjects\Cart\TaxBreakdownAmount;

class CalculateTax
{
    /**
     * Called just before cart totals are calculated.
     *
     * @param  Closure(Cart):mixed  $next
     */
    public function handle(Cart $cart, Closure $next): mixed
    {
        /** @var Cart $cart */
        $taxBreakDownAmounts = collect();

        foreach ($cart->lines as $cartLine) {
            $subTotal = $cartLine->subTotalDiscounted ?: $cartLine->subTotal;

            $taxBreakDownResult = Taxes::setShippingAddress($cart->shippingAddress)
                ->setBillingAddress($cart->billingAddress)
                ->setCurrency($cart->currency)
                ->setPurchasable($cartLine->purchasable)
                ->setCartLine($cartLine)
                ->setTaxZone($cart->taxZone)
                ->getBreakdown($subTotal->value);

            $taxBreakDownAmounts = $taxBreakDownAmounts->merge(
                $taxBreakDownResult->amounts
            );

            $taxAmount = PriceValue::sum($taxBreakDownResult->amounts->pluck('price'), $cart->currency);

            $cartLine->taxBreakdown = $taxBreakDownResult;

            $cart->taxTotal = $taxAmount;
            $cartLine->taxAmount = $taxAmount;

            $cartLine->total = prices_inc_tax()
                ? $subTotal
                : $subTotal->add($taxAmount);
        }

        $taxBreakDown = new TaxBreakdown($taxBreakDownAmounts);

        $taxTotal = PriceValue::sum($cart->lines->pluck('taxAmount'), $cart->currency);
        $taxBreakDownAmounts = $taxBreakDown->amounts->filter()->flatten();

        $shippingOption = $cart->shippingOptionOverride ?: ShippingManifest::getShippingOption($cart);

        if ($shippingOption) {
            $shippingSubTotal = PriceValue::sum($cart->shippingBreakdown->items->pluck('price'), $cart->currency);

            $shippingTax = Taxes::setShippingAddress($cart->shippingAddress)
                ->setCurrency($cart->currency)
                ->setPurchasable($shippingOption)
                ->setTaxZone($cart->taxZone)
                ->getBreakdown($shippingSubTotal->value);

            $shippingTaxTotal = PriceValue::sum($shippingTax->amounts->pluck('price'), $cart->currency);

            $cart->shippingTaxTotal = $shippingTaxTotal;
            $taxTotal = $taxTotal->add($shippingTaxTotal);

            $taxBreakDownAmounts = $taxBreakDownAmounts->merge(
                $shippingTax->amounts
            );

            $shippingTotal = prices_inc_tax()
                ? $shippingSubTotal
                : $shippingSubTotal->add($shippingTaxTotal);

            $cart->shippingTotal = $shippingTotal;

            if ($cart->shippingAddress && ! $cart->shippingOptionOverride) {
                $cart->shippingAddress->taxBreakdown = $shippingTax;
                $cart->shippingAddress->shippingTaxTotal = $shippingTaxTotal;
                $cart->shippingAddress->shippingTotal = $shippingTotal;
            }
        }

        $cart->taxTotal = $taxTotal;

        // Need to include shipping tax breakdown...
        $cart->taxBreakdown = new TaxBreakdown(
            $taxBreakDownAmounts->groupBy('identifier')->map(function ($amounts) use ($cart) {
                return new TaxBreakdownAmount(
                    price: PriceValue::sum($amounts->pluck('price'), $cart->currency),
                    percentage: $amounts->first()->percentage,
                    description: $amounts->first()->description,
                    identifier: $amounts->first()->identifier
                );
            })
        );

        return $next($cart);
    }
}
