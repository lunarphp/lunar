<?php

namespace GetCandy\DiscountTypes;

use GetCandy\DataTypes\Price;

class Coupon
{
    protected $data = [];

    /**
     * Set the data for the discount to user.
     *
     * @param  array  $data
     * @return self
     */
    public function data(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Return the name of the discount.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Coupon';
    }

    /**
     * Called just before cart totals are calculated.
     *
     * @return CartLine
     */
    public function execute(CartLine $cartLine): CartLine
    {
        $cartCoupon = strtoupper($cartLine->cart->meta->coupon ?? null);
        $conditionCoupon = strtoupper($this->data['coupon'] ?? null);

        $passes = $cartCoupon && ($cartCoupon === $conditionCoupon);

        if (! $passes) {
            return $cartLine;
        }

        if ($this->data['type'] == 'percentage') {
            return $this->applyPercentage(
                value: $this->data['value'],
                cartLine: $cartLine
            );
        }

        return $cartLine;
    }

    /**
     * Apply the percentage to the cart line.
     *
     * @param  int  $value
     * @param  CartLine  $cartLine
     * @return CartLine
     */
    private function applyPercentage($value, $cartLine): CartLine
    {
        $subTotal = $cartLine->subTotal->value;
        $amount = (int) round($subTotal * ($value / 100));

        $cartLine->discountTotal = new Price(
            $amount,
            $cartLine->cart->currency,
            1
        );

        return $cartLine;
    }
}
