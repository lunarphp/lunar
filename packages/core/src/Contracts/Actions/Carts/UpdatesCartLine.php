<?php

namespace Lunar\Core\Contracts\Actions\Carts;

interface UpdatesCartLine
{
    public function execute(
        int $cartLineId,
        int $quantity,
        ?array $meta = null
    ): void;
}
