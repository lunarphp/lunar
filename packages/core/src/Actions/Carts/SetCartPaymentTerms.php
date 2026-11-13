<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Contracts\Actions\Carts\SetsCartPaymentTerms;
use Lunar\Core\Enums\PaymentTerms;
use Lunar\Core\Models\Cart;

class SetCartPaymentTerms implements SetsCartPaymentTerms
{
    public function execute(Cart $cart, ?PaymentTerms $terms): Cart
    {
        /** @var Cart $cart */
        $cart->update(['payment_terms' => $terms]);

        return $cart;
    }
}
