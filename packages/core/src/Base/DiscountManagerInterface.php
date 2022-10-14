<?php

namespace Lunar\Base;

use Illuminate\Support\Collection;
use Lunar\Base\DataTransferObjects\CartDiscount;
use Lunar\Models\Cart;

interface DiscountManagerInterface
{
    /**
     * Add a discount type by classname
     *
     * @param string $classname
     *
     * @return self
     */
    public function addType($classname): self;

    /**
     * Return the available discount types.
     *
     * @return Collection
     */
    public function getTypes(): Collection;

    /**
     * Add an applied discount
     *
     * @param CartDiscount $cartDiscount
     *
     * @return self
     */
    public function addApplied(CartDiscount $cartDiscount): self;

    /**
     * Return the applied discounts
     *
     * @return Collection
     */
    public function getApplied(): Collection;

    /**
     * Apply discounts for a given cart.
     *
     * @param Cart $cart
     *
     * @return Cart
     */
    public function apply(Cart $cart): Cart;

    /**
     * Validate a given coupon against all system discounts.
     *
     * @param string $coupon
     *
     * @return bool
     */
    public function validateCoupon(string $coupon): bool;
}
