<?php

namespace Lunar\Core\Actions\Carts;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Actions\Carts\CalculatesLine;
use Lunar\Core\Contracts\Actions\Carts\CalculatesLineSubtotal;
use Lunar\Core\Contracts\Addressable;
use Lunar\Core\Contracts\TaxManager;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\CartLine;

class CalculateLine implements CalculatesLine
{
    public function __construct(
        protected CalculatesLineSubtotal $calculatesLineSubtotal,
        protected TaxManager $taxManager,
    ) {}

    /**
     * Execute the action.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $customerGroups
     */
    public function execute(
        CartLine $cartLine,
        Collection $customerGroups,
        ?Addressable $shippingAddress = null,
        ?Addressable $billingAddress = null
    ): CartLine {
        /** @var CartLine $cartLine */
        $purchasable = $cartLine->purchasable;
        $cart = $cartLine->cart;

        $cartLine = $this->calculatesLineSubtotal->execute($cartLine, $customerGroups);

        if (! $cartLine->discountTotal) {
            $cartLine->discountTotal = new PriceValue(0, $cart->currency);
        }

        $subTotal = $cartLine->subTotal->subtract($cartLine->discountTotal);

        $taxBreakDown = $this->taxManager->setShippingAddress($shippingAddress)
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
