<?php

namespace Lunar\Core\Actions\Carts;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Actions\Carts\CalculatesLineSubtotal;
use Lunar\Core\Contracts\PricingManager;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Modifiers\CartLineModifiers;

class CalculateLineSubtotal implements CalculatesLineSubtotal
{
    public function __construct(
        protected PricingManager $pricing,
    ) {}

    /**
     * Execute the action.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $customerGroups
     */
    public function execute(
        CartLine $cartLine,
        Collection $customerGroups
    ): CartLine {
        /** @var CartLine $cartLine */
        $purchasable = $cartLine->purchasable;
        $cart = $cartLine->cart;
        $unitQuantity = $purchasable->getUnitQuantity();

        $price = $cartLine->unitPrice;
        $priceInclTax = $cartLine->unitPriceInclTax;

        // we check if any cart line modifiers have already specified a unit price in their calculating() method
        if (! $price instanceof PriceValue) {
            $priceResponse = $this->pricing->currency($cart->currency)
                ->qty($cartLine->quantity)
                ->currency($cart->currency)
                ->customerGroups($customerGroups)
                ->for($purchasable)
                ->get();

            $price = new PriceValue(
                (int) $priceResponse->matched->price,
                $cart->currency,
            );

            $priceInclTax = new PriceValue(
                $priceResponse->matched->priceIncTax($cart->taxZone)->value,
                $cart->currency,
            );
        }

        $unitPrice = (int) round($price->value / $unitQuantity);
        $unitPriceInclTax = (int) round($priceInclTax->value / $unitQuantity);

        $cartLine->subTotal = new PriceValue($unitPrice * $cartLine->quantity, $cart->currency);
        $cartLine->unitPrice = new PriceValue($unitPrice, $cart->currency);
        $cartLine->unitPriceInclTax = new PriceValue($unitPriceInclTax, $cart->currency);

        $pipeline = app(Pipeline::class)
            ->through(
                $this->getModifiers()->toArray()
            );

        return $pipeline->send($cartLine)->via('subtotalled')->thenReturn();
    }

    /**
     * Return the cart line modifiers.
     *
     * @return Collection
     */
    private function getModifiers()
    {
        return app(CartLineModifiers::class)->getModifiers();
    }
}
