<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Models\Contracts\Cart as CartContract;

interface GeneratesFingerprint
{
    public function execute(CartContract $cart): string;
}
