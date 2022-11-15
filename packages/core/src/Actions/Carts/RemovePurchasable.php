<?php

namespace Lunar\Actions\Carts;

use Illuminate\Support\Facades\DB;
use Lunar\Actions\AbstractAction;
use Lunar\Exceptions\CartLineIdMismatchException;
use Lunar\Models\Cart;

class RemovePurchasable extends AbstractAction
{
    /**
     * Execute the action
     *
     * @param  Cart  $cart
     * @param  int  $cartLineId
     * @return bool
     *
     * @throws CartLineIdMismatchException
     */
    public function execute(
        Cart $cart,
        int $cartLineId
    ): self {
        DB::transaction(function () use ($cart, $cartLineId) {
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

        return $this;
    }
}
