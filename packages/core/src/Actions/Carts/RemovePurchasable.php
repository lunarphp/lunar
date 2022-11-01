<?php

namespace Lunar\Actions\Carts;

use Illuminate\Support\Facades\DB;
use Lunar\Actions\AbstractAction;
use Lunar\Exceptions\CartLineIdMismatchException;
use Lunar\Models\Cart;
use Yab\MySQLScout\Engines\Modes\Boolean;

class RemovePurchasable extends AbstractAction
{
    /**
     * Execute the action
     *
     * @param Cart $cart
     * @param integer $cartLineId
     *
     * @throws CartLineIdMismatchException
     *
     * @return Boolean
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
