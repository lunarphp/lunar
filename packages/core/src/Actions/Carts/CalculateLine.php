<?php

namespace GetCandy\Actions\Carts;

use GetCandy\Base\Addressable;
use GetCandy\DataTypes\Price;
use GetCandy\Facades\Pricing;
use GetCandy\Facades\Taxes;
use GetCandy\Models\CartLine;
use Illuminate\Support\Collection;

class CalculateLine
{
    /**
     * Execute the action.
     *
     * @param  \GetCandy\Models\CartLine  $cartLine
     * @param  \Illuminate\Database\Eloquent\Collection  $customerGroups
     * @return \GetCandy\Models\CartLine
     */
    public function execute(
        CartLine $cartLine,
        Collection $customerGroups,
        Addressable $shippingAddress = null,
        Addressable $billingAddress = null
    ) {
        $purchasable = $cartLine->purchasable;
        $cart = $cartLine->cart;
        $unitQuantity = $purchasable->getUnitQuantity();

        $cartLine = app(CalculateLineSubtotal::class)->execute($cartLine, $customerGroups);

        if (! $cartLine->discountTotal) {
            $cartLine->discountTotal = new Price(0, $cart->currency, $unitQuantity);
        }

        $subTotal = $cartLine->subTotal->value - $cartLine->discountTotal->value;

        $taxBreakDown = Taxes::setShippingAddress($shippingAddress)
            ->setBillingAddress($billingAddress)
            ->setCurrency($cart->currency)
            ->setPurchasable($purchasable)
            ->setCartLine($cartLine)
            ->getBreakdown($subTotal);

        $taxTotal = $taxBreakDown->amounts->sum('price.value');

        $cartLine->taxBreakdown = $taxBreakDown;
        $cartLine->taxAmount = new Price($taxTotal, $cart->currency, $unitQuantity);
        $cartLine->total = new Price($subTotal + $taxTotal, $cart->currency, $unitQuantity);

        return $cartLine;
    }
}
