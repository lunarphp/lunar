<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Actions\AbstractAction;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Exceptions\InvalidCartLineQuantityException;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Contracts\Cart as CartContract;

class AddOrUpdatePurchasable extends AbstractAction
{
    /**
     * Execute the action.
     */
    public function execute(
        CartContract $cart,
        Purchasable $purchasable,
        int $quantity = 1,
        array $meta = []
    ): self {
        throw_if(! $quantity, InvalidCartLineQuantityException::class);

        /** @var Cart $cart */
        $existing = app(
            config('lunar.cart.actions.get_existing_cart_line', GetExistingCartLine::class)
        )->execute(
            cart: $cart,
            purchasable: $purchasable,
            meta: $meta
        );

        if ($existing) {
            $existing->update([
                'quantity' => $existing->quantity + $quantity,
            ]);

            return $this;
        }

        $cart->lines()->create([
            'purchasable_id' => $purchasable->id,
            'purchasable_type' => $purchasable->getMorphClass(),
            'quantity' => $quantity,
            'meta' => $meta,
        ]);

        return $this;
    }
}
