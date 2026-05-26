<?php

namespace Lunar\Core\Actions\Carts;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Addressable;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Facades\Taxes;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Contracts\CartLine as CartLineContract;

class CalculateLine
{
    /**
     * Execute the action.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $customerGroups
     * @return CartLine
     */
    public function execute(
        CartLineContract $cartLine,
        Collection $customerGroups,
        ?Addressable $shippingAddress = null,
        ?Addressable $billingAddress = null
    ) {
        /** @var CartLine $cartLine */
        $purchasable = $cartLine->purchasable;
        $cart = $cartLine->cart;

        $cartLine = app(CalculateLineSubtotal::class)->execute($cartLine, $customerGroups);

        if (! $cartLine->discountTotal) {
            $cartLine->discountTotal = new PriceValue(0, $cart->currency);
        }

        $subTotal = $cartLine->subTotal->subtract($cartLine->discountTotal);

        $taxBreakDown = Taxes::setShippingAddress($shippingAddress)
            ->setBillingAddress($billingAddress)
            ->setCurrency($cart->currency)
            ->setPurchasable($purchasable)
            ->setCartLine($cartLine)
            ->getBreakdown($subTotal->value);

        $taxAmount = PriceValue::sum($taxBreakDown->amounts->pluck('price'), $cart->currency);

        $cartLine->taxBreakdown = $taxBreakDown;
        $cartLine->taxAmount = $taxAmount;
        $cartLine->total = $subTotal->add($taxAmount);

        return $cartLine;
    }
}
