<?php

namespace Lunar\Core\Actions\Carts;

use Illuminate\Support\Collection;
use Lunar\Core\Base\Addressable;
use Lunar\Core\DataTypes\Price;
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
