<?php

namespace Lunar\DiscountTypes;

use Lunar\Base\DataTransferObjects\CartDiscount;
use Lunar\DataTypes\Price;
use Lunar\Facades\Discounts;
use Lunar\Models\CartLine;
use Lunar\Models\Collection;

class Coupon extends AbstractDiscountType
{
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
        $data = $this->discount->data;

        $cartCoupon = strtoupper($cartLine->cart->meta->coupon ?? null);
        $conditionCoupon = strtoupper($data['coupon'] ?? null);

        $passes = $cartCoupon && ($cartCoupon === $conditionCoupon);

        if (! $passes) {
            return $cartLine;
        }

        $collectionIds = $this->discount->collections->pluck('id');

        if ($collectionIds->count()) {
            $passes = $cartLine->purchasable->product()->whereHas('collections', function ($query) use ($collectionIds) {
                $query->whereIn((new Collection)->getTable().'.id', $collectionIds);
            })->exists();
        }

        if (! $passes) {
            return $cartLine;
        }

        $cartLine->discount = $this->discount;

        Discounts::addApplied(
            new CartDiscount($cartLine, $this->discount)
        );

        if ($data['fixed_value']) {
            return $this->applyFixedValue(
                values: $data['fixed_values'],
                cartLine: $cartLine
            );
        }

        return $this->applyPercentage(
            value: $data['percentage'],
            cartLine: $cartLine
        );
    }

    private function applyFixedValue(array $values, CartLine $cartLine): CartLine
    {
        $currency = $cartLine->cart->currency;

        $value = ($values[$currency->code] ?? 0) * 100;

        if (! $value) {
            return $cartLine;
        }

        $amount = (int) (round($value / $cartLine->cart->lines->count(), 2));

        $cartLine->discountTotal = new Price(
            $amount,
            $currency,
            1
        );

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
