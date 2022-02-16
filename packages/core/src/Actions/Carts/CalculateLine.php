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

        $priceResponse = Pricing::currency($cart->currency)
            ->qty($cartLine->quantity)
            ->currency($cart->currency)
            ->customerGroups($customerGroups)
            ->for($purchasable);

        $price = new Price(
            $priceResponse->matched->price->value,
            $cart->currency,
            $purchasable->getUnitQuantity()
        );

        $unitPrice = (int) (round(
            $price->decimal / $purchasable->getUnitQuantity(),
            $cart->currency->decimal_places
        ) * $cart->currency->factor);

        $subTotal = $unitPrice * $cartLine->quantity;

        $taxBreakDown = Taxes::setShippingAddress($shippingAddress)
            ->setBillingAddress($billingAddress)
            ->setCurrency($cart->currency)
            ->setPurchasable($purchasable)
            ->getBreakdown($subTotal);

        $taxTotal = $taxBreakDown->sum('total.value');

        $cartLine->taxBreakdown = $taxBreakDown;
        $cartLine->subTotal = new Price($subTotal, $cart->currency, $unitQuantity);
        $cartLine->taxAmount = new Price($taxTotal, $cart->currency, $unitQuantity);
        $cartLine->total = new Price($subTotal + $taxTotal, $cart->currency, $unitQuantity);
        $cartLine->unitPrice = new Price($unitPrice, $cart->currency, $unitQuantity);
        $cartLine->discountTotal = new Price(0, $cart->currency, $unitQuantity);

        return $cartLine;
    }
}
