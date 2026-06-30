<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Contracts\LunarUser;
use Lunar\Core\Models\Cart;

interface AssociatesUser
{
    public function execute(
        Cart $cart,
        LunarUser $user,
        string $policy = 'merge'
    ): void;
}
