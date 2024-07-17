<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\DataTypes\Price;
use Lunar\Facades\ShippingManifest;
use Lunar\Facades\Taxes;
use Lunar\Models\Cart;

final class ApplyShipping
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function handle(Cart $cart, Closure $next)
    {
        $shippingBreakdown = $cart->shippingBreakdown ?: new ShippingBreakdown;

        $shippingOption = $cart->shippingOptionOverride ?: ShippingManifest::getShippingOption($cart);

        $shippingSubTotal = $shippingBreakdown->items->sum('price.value');
        $shippingTotal = $shippingSubTotal;

        if ($shippingOption) {
            if ($cart->shippingOptionOverride) {
                $shippingBreakdown->items = collect();
            }

            $shippingBreakdown->items->put(
                $shippingOption->getIdentifier(),
                new ShippingBreakdownItem(
                    name: $shippingOption->getName(),
                    identifier: $shippingOption->getIdentifier(),
                    price: $shippingOption->price,
                )
            );

            $shippingSubTotal = $shippingBreakdown->items->sum('price.value');

            $shippingTax = Taxes::setShippingAddress($cart->shippingAddress)
                ->setCurrency($cart->currency)
                ->setPurchasable($shippingOption)
                ->getBreakdown($shippingSubTotal);

            $shippingTaxTotal = new Price(
                $shippingTax->amounts->sum('price.value'),
                $cart->currency,
                1
            );

            if (! prices_inc_tax()) {
                $shippingTotal += $shippingTaxTotal?->value;
            }

            if ($cart->shippingAddress && ! $cart->shippingBreakdown) {
                $cart->shippingAddress->taxBreakdown = $shippingTax;
                $cart->shippingAddress->shippingSubTotal = new Price($shippingOption->price->value, $cart->currency, 1);
                $cart->shippingAddress->shippingTaxTotal = $shippingTaxTotal;
                $cart->shippingAddress->shippingTotal = new Price($shippingTotal, $cart->currency, 1);
            }
        }

        $cart->shippingBreakdown = $shippingBreakdown;

        $cart->shippingTotal = new Price(
            $shippingTotal,
            $cart->currency,
            1
        );

        $cart->shippingSubTotal = new Price(
            $shippingSubTotal,
            $cart->currency,
            1
        );

        return $next($cart);
    }
}
