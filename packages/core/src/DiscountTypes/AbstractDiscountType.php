<?php

namespace Lunar\DiscountTypes;

use Lunar\Base\DiscountTypeInterface;
use Lunar\Models\Discount;

abstract class AbstractDiscountType implements DiscountTypeInterface
{
    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    protected Discount $discount;

    /**
     * Set the data for the discount to user.
     *
     * @param  array  $data
     * @return self
     */
    public function with(Discount $discount): self
    {
        $this->discount = $discount;

        return $this;
    }
}
