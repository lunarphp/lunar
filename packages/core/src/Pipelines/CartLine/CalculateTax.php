<?php

namespace Lunar\Pipelines\CartLine;

use Closure;
use Lunar\DataTypes\Price;
use Lunar\Facades\Taxes;
use Lunar\Models\CartLine;

class CalculateTax
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function handle(CartLine $cartLine, Closure $next)
    {
        $subTotal = $cartLine->subTotal?->value;

        $unitQuantity = $cartLine->purchasable->getUnitQuantity();

        if (! is_null($cartLine->subTotalDiscounted?->value)) {
            $subTotal = $cartLine->subTotalDiscounted?->value;
        }

        $taxBreakDownResult = Taxes::setShippingAddress($cartLine->cart->shippingAddress)
            ->setBillingAddress($cartLine->cart->billingAddress)
            ->setCurrency($cartLine->cart->currency)
            ->setPurchasable($cartLine->purchasable)
            ->setCartLine($cartLine)
            ->getBreakdown($subTotal);

        $taxTotal = $taxBreakDownResult->amounts->sum('price.value');

        $cartLine->taxBreakdown = $taxBreakDownResult;

        $cartLine->taxAmount = new Price($taxTotal, $cartLine->cart->currency, $unitQuantity);

        if (prices_inc_tax()) {
            $cartLine->total = new Price($subTotal, $cartLine->cart->currency, $unitQuantity);
        } else {
            $cartLine->total = new Price($subTotal + $taxTotal, $cartLine->cart->currency, $unitQuantity);
        }

        return $next($cartLine);
    }
}
