<?php

namespace Lunar\Core\Validation;

use Lunar\Core\Contracts\CouponValidator as CouponValidatorContract;
use Lunar\Core\Models\Discount;

class CouponValidator implements CouponValidatorContract
{
    public function validate(string $coupon): bool
    {
        $discount = Discount::query()
            ->active()
            ->where(function ($query) {
                $query->whereNull('max_uses')
                    ->orWhereRaw('uses < max_uses');
            })->where('coupon', '=', strtoupper($coupon))->first();

        if (! $discount) {
            return false;
        }

        if ($discount->max_uses_per_user && $user = auth()->user()) {
            $uses = $discount->users()
                ->whereUserId($user->getKey())
                ->count();

            if ($uses >= $discount->max_uses_per_user) {
                return false;
            }
        }

        return true;
    }
}
