<?php

namespace Lunar\Core\Validation;

use Illuminate\Support\Str;
use Lunar\Core\Contracts\Actions\Discounts\CountsCustomerRedemptions;
use Lunar\Core\Contracts\CartSession;
use Lunar\Core\Contracts\CouponValidator as CouponValidatorContract;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Coupon;

class CouponValidator implements CouponValidatorContract
{
    public function __construct(
        protected CartSession $cartSession,
        protected CountsCustomerRedemptions $countsCustomerRedemptions,
    ) {}

    public function validate(string $coupon, ?Cart $cart = null): bool
    {
        $coupon = Coupon::query()
            ->usable()
            ->where('code', Str::upper($coupon))
            ->whereHas('discount', fn ($query) => $query->active()->usable())
            ->with('discount')
            ->first();

        if (! $coupon) {
            return false;
        }

        $discount = $coupon->discount;

        if (! $discount->max_uses_per_user) {
            return true;
        }

        $cart ??= $this->cartSession->current();

        // With no cart there is no customer to count yet; the discount conditions hold the limit once there is.
        if (! $cart) {
            return true;
        }

        return $this->countsCustomerRedemptions->execute($discount, $cart) < $discount->max_uses_per_user;
    }
}
