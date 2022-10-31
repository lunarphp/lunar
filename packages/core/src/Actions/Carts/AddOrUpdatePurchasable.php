<?php

namespace Lunar\Actions\Carts;

use Lunar\Actions\AbstractAction;
use Lunar\Base\Purchasable;
use Lunar\Models\Cart;

class AddOrUpdatePurchasable extends AbstractAction
{
    /**
     * Execute the action.
     *
     * @param  \Lunar\Models\CartLine  $cartLine
     * @param  \Illuminate\Database\Eloquent\Collection  $customerGroups
     * @return \Lunar\Models\CartLine
     */
    public function execute(
        Cart $cart,
        Purchasable $purchasable,
        int $quantity = 1,
        array $meta = []
    ): self {
        // Do we already have this line?
        $existing = $cart->load('lines')->lines->first(function ($line) use ($purchasable, $meta) {
            return $line->purchasable_id == $purchasable->id &&
            $line->purchasable_type == get_class($purchasable) &&
            array_diff((array) ($line->meta ?? []), $meta ?? []) == [] &&
            array_diff($meta ?? [], (array) ($line->meta ?? [])) == [];
        });

        if ($existing) {
            $existing->update([
                'quantity' => $existing->quantity + $quantity,
            ]);

            return true;
        }

        $cart->lines()->create([
            'purchasable_id' => $purchasable->id,
            'purchasable_type' => get_class($purchasable),
            'quantity' => $quantity,
            'meta' => $meta,
        ]);

        return $this;
    }
}
