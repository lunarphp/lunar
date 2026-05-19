<?php

namespace Lunar\Base\Validation;

use Lunar\Models\Discount;

class CouponValidator implements CouponValidatorInterface
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
