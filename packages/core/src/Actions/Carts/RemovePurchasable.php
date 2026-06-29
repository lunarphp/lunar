<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Contracts\Actions\Carts\RemovesPurchasable;
use Lunar\Core\Exceptions\CartLineIdMismatchException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;

class RemovePurchasable implements RemovesPurchasable
{
    /**
     * Execute the action.
     *
     * @throws CartLineIdMismatchException
     */
    public function execute(
        Cart $cart,
        int $cartLineId
    ): void {
        /** @var Cart $cart */
        DB::transaction(function () use ($cart, $cartLineId) {
            /** @var CartLine|null $line */
            $line = $cart->lines()->whereId($cartLineId)->first();

            if (! $line) {
                // If we're trying to remove a line that does not
                // belong to this cart, throw an exception.
                throw new CartLineIdMismatchException(
                    __('lunar::exceptions.cart_line_id_mismatch')
                );
            }

            $line->delete();
        });
    }
}
