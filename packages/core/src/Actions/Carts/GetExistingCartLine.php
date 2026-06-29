<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Contracts\Actions\Carts\GetsExistingCartLine;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Utils\Arr;

class GetExistingCartLine implements GetsExistingCartLine
{
    /**
     * Execute the action
     */
    public function execute(
        Cart $cart,
        Purchasable $purchasable,
        array $meta = []
    ): ?CartLine {
        /** @var Cart $cart */

        // Get all possible cart lines
        $lines = $cart->lines()
            ->wherePurchasableType(
                $purchasable->getMorphClass()
            )->wherePurchasableId($purchasable->id)
            ->get();

        return $lines->first(function ($line) use ($meta) {
            $diff = Arr::diff($line->meta, $meta);

            return empty($diff->new) && empty($diff->edited) && empty($diff->removed);
        });
    }
}
