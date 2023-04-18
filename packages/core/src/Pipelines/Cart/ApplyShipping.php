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
        $shippingSubTotal = 0;
        $shippingBreakdown = $cart->shippingBreakdown ?: new ShippingBreakdown;

        if ($shippingOption = $this->getShippingOption($cart)) {
            $shippingTax = Taxes::setShippingAddress($cart->shippingAddress)
                ->setCurrency($cart->currency)
                ->setPurchasable($shippingOption)
                ->getBreakdown($shippingOption->price->value);

            $shippingBreakdown->items->push(
                new ShippingBreakdownItem(
                    name: $shippingOption->getName(),
                    identifier: $shippingOption->getIdentifier(),
                    price: $shippingOption->price,
                )
            );

            $cart->shippingBreakdown = $shippingBreakdown;

            $shippingSubTotal = $shippingOption->price->value;
            $shippingTaxTotal = $shippingTax->amounts->sum('price.value');
            $shippingTotal = $shippingSubTotal + $shippingTaxTotal;

            $cart->shippingAddress->taxBreakdown = $shippingTax;
            $cart->shippingAddress->shippingTotal = new Price($shippingTotal, $cart->currency, 1);
            $cart->shippingAddress->shippingTaxTotal = new Price($shippingTaxTotal, $cart->currency, 1);
            $cart->shippingAddress->shippingSubTotal = new Price($shippingOption->price->value, $cart->currency, 1);

            $shippingSubTotal = $shippingOption->price->value;
        }

        // $cart->shippingTotal = new Price(
        //     $cart->shippingBreakdown->items->sum('price.value'),
        //     $cart->currency,
        //     1
        // );

        return $next($cart);
    }

    private function getShippingOption(Cart $cart)
    {
        if (! $cart->shippingAddress) {
            return null;
        }

        return ShippingManifest::getOptions($cart)->first(function ($option) use ($cart) {
            return $option->getIdentifier() == $cart->shippingAddress->shipping_option;
        });
    }
}
