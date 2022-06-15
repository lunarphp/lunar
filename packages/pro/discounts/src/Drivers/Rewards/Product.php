<?php

namespace GetCandy\Discounts\Drivers\Rewards;

use GetCandy\DataTypes\Price;
use GetCandy\Discounts\Models\DiscountReward;
use GetCandy\Facades\Pricing;
use GetCandy\Models\CartLine;

class Product
{
    protected DiscountReward $reward;

    public function with(DiscountReward $discountReward)
    {
        $this->reward = $discountReward;

        return $this;
    }

    public function apply(CartLine $cartLine): CartLine
    {
        $purchasable = $this->reward->purchasables->first(function ($purchasable) use ($cartLine) {
            return $cartLine->purchasable_id == $purchasable->purchasable_id;
        })?->purchasable;

        if (! $purchasable) {
            return $cartLine;
        }

        $unitPrice = Pricing::for($purchasable)->get();

        $cartLine->discountTotal = new Price(
            $unitPrice->matched->price->value,
            $cartLine->cart->currency,
            1
        );

        return $cartLine;
    }
}
