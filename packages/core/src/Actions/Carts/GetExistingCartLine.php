<?php

namespace Lunar\Actions\Carts;

use Lunar\Actions\AbstractAction;
use Lunar\Base\Purchasable;
use Lunar\Models\Cart;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Models\Contracts\CartLine as CartLineContract;
use Lunar\Utils\Arr;

class GetExistingCartLine extends AbstractAction
{
    /**
     * Execute the action
     */
    public function execute(
        CartContract $cart,
        Purchasable $purchasable,
        array $meta = []
    ): ?CartLineContract {
        /** @var Cart $cart */

        // Get all possible cart lines
        $lines = $cart->lines()
            ->wherePurchasableType(
                $purchasable->getMorphClass()
            )->wherePurchasableId($purchasable->id)
            ->get();

        return $lines->first(function ($line) use ($meta) {
            $diff = Arr::diff($line->meta, $meta);

            return empty($diff->new) && empty($diff->edited) & empty($diff->removed);
        });
    }
}
