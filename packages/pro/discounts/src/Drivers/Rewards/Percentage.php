<?php

namespace GetCandy\Discounts\Drivers\Rewards;

use GetCandy\Discounts\Models\DiscountCondition;
use GetCandy\Discounts\Models\DiscountReward;
use GetCandy\Models\Cart;
use GetCandy\Models\CartLine;

class Percentage
{
    protected DiscountReward $reward;

    public function with(DiscountReward $discountReward)
    {
        $this->reward = $discountReward;

        return $this;
    }

    public function apply(CartLine $cartLine): bool
    {
        $percentage = $this->reward->data->amount ?? 0;

        $subTotal = $cartLine->subTotal;

        dd($cartLine);
        dd('yo!');
    }
}
