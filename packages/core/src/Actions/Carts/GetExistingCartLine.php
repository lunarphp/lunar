<?php

namespace Lunar\Actions\Carts;

use Lunar\Actions\AbstractAction;
use Lunar\Base\Purchasable;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Utils\Arr;

class GetExistingCartLine extends AbstractAction
{
    /**
     * Execute the action
     *
     * @param  Cart  $cart
     * @param  Purchasable  $purchasable
     * @param  array  $meta
     * @return CartLine|null
     */
    public function execute(
        Cart $cart,
        Purchasable $purchasable,
        array $meta = []
    ): CartLine|null {
        // Get all possible cart lines
        $lines = $cart->lines()
            ->wherePurchasableType(
                get_class($purchasable)
            )->wherePurchasableId($purchasable->id)
            ->get();

        return $lines->first(function ($line) use ($meta) {
            $diff = Arr::diff($line->meta, $meta);

            return empty($diff->new) && empty($diff->edited) & empty($diff->removed);
        });
    }
}
