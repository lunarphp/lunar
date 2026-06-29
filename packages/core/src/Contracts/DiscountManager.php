<?php

namespace Lunar\Core\Contracts;

use Illuminate\Support\Collection;
use Lunar\Core\DataObjects\CartDiscount;
use Lunar\Core\Models\Cart;

interface DiscountManager
{
    /**
     * Add a discount type by classname
     *
     * @param  string  $classname
     */
    public function addType($classname): self;

    /**
     * Return the available discount types.
     */
    public function getTypes(): Collection;

    /**
     * Add an applied discount
     */
    public function addApplied(CartDiscount $cartDiscount): self;

    /**
     * Return the applied discounts
     */
    public function getApplied(): Collection;

    /**
     * Apply discounts for a given cart.
     */
    public function apply(Cart $cart): Cart;

    /**
     * Validate a given coupon against all system discounts.
     */
    public function validateCoupon(string $coupon): bool;
}
