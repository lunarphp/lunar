<?php

namespace Lunar\Core\Contracts\Actions\Discounts;

use Lunar\Core\Models\Discount;

interface DeletesDiscount
{
    public function execute(Discount $discount): void;
}
