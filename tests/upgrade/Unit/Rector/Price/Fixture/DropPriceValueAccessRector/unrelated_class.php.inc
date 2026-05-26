<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\DropPriceValueAccessRector;

final class Wallet
{
    public object $total;
}

function leave_unrelated_alone(Wallet $wallet): mixed
{
    return $wallet->total->value;
}

?>
