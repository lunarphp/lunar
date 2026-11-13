<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Enums\PaymentTerms;
use Lunar\Core\Models\Cart;

interface SetsCartPaymentTerms
{
    public function execute(Cart $cart, ?PaymentTerms $terms): Cart;
}
