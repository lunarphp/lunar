<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Models\Cart;

interface GeneratesFingerprint
{
    public function execute(Cart $cart): string;
}
