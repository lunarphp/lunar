<?php

namespace Lunar\Core\Actions\Carts;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Actions\Carts\CalculatesLine;
use Lunar\Core\Contracts\Actions\Carts\CalculatesLineSubtotal;
use Lunar\Core\Contracts\Addressable;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Facades\Taxes;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Contracts\CartLine as CartLineContract;

class CalculateLine implements CalculatesLine
{
    public function __construct(
        protected CalculatesLineSubtotal $calculatesLineSubtotal,
    ) {}

    /**
     * Execute the action.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $customerGroups
     */
    public function execute(
        CartLineContract $cartLine,
        Collection $customerGroups,
        ?Addressable $shippingAddress = null,
        ?Addressable $billingAddress = null
    ): CartLineContract {
        /** @var CartLine $cartLine */
        $purchasable = $cartLine->purchasable;
        $cart = $cartLine->cart;

        $cartLine = $this->calculatesLineSubtotal->execute($cartLine, $customerGroups);

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
