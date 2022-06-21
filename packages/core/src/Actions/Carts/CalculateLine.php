<?php

namespace GetCandy\Actions\Carts;

use GetCandy\Base\Addressable;
use GetCandy\Base\CartLineModifiers;
use GetCandy\DataTypes\Price;
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

        $taxBreakDown = Taxes::setShippingAddress($shippingAddress)
            ->setBillingAddress($billingAddress)
            ->setCurrency($cart->currency)
            ->setPurchasable($purchasable)
            ->setCartLine($cartLine)
            ->getBreakdown($cartLine->subTotal->value - $cartLine->discountTotal->value);

        $taxTotal = $taxBreakDown->amounts->sum('price.value');

        $cartLine->taxBreakdown = $taxBreakDown;

        $cartLine->total = new Price(
            ($cartLine->subTotal->value - $cartLine->discountTotal->value) + $taxTotal,
            $cart->currency,
            $unitQuantity
        );
        $cartLine->taxAmount = new Price($taxTotal, $cart->currency, $unitQuantity);

        return $cartLine;
    }

    /**
     * Return the cart line modifiers.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getModifiers()
    {
        return app(CartLineModifiers::class)->getModifiers();
    }
}
