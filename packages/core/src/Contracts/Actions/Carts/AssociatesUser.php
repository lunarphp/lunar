<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Contracts\LunarUser;
use Lunar\Core\Models\Contracts\Cart as CartContract;

interface AssociatesUser
{
    public function execute(
        CartContract $cart,
        LunarUser $user,
        string $policy = 'merge'
    ): void;
}
