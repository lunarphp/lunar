<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Contracts\Actions\Carts\AddsOrUpdatesPurchasable;
use Lunar\Core\Contracts\Actions\Carts\GetsExistingCartLine;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Exceptions\InvalidCartLineQuantityException;
use Lunar\Core\Models\Cart;

class AddOrUpdatePurchasable implements AddsOrUpdatesPurchasable
{
    public function __construct(
        protected GetsExistingCartLine $getsExistingCartLine,
    ) {}

    /**
     * Execute the action.
     */
    public function execute(
        Cart $cart,
        Purchasable $purchasable,
        int $quantity = 1,
        array $meta = []
    ): void {
        throw_if(! $quantity, InvalidCartLineQuantityException::class);

        $existing = $this->getsExistingCartLine->execute(
            cart: $cart,
            purchasable: $purchasable,
            meta: $meta
        );

        if ($existing) {
            $existing->update([
                'quantity' => $existing->quantity + $quantity,
            ]);

            return;
        }

        $cart->lines()->create([
            'purchasable_id' => $purchasable->id,
            'purchasable_type' => $purchasable->getMorphClass(),
            'quantity' => $quantity,
            'meta' => $meta,
        ]);
    }
}
